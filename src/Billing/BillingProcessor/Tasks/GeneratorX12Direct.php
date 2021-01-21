<?php

/**
 * This class represents the task that compiles claims into
 * x-12 batch files, one for each insurance/x-12 pair.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Daniel Pflieger <daniel@mi-squared.com>, <daniel@growlingflea.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\BillingProcessor;
use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\X125010837P;
use OpenEMR\Common\Csrf\CsrfUtils;

class GeneratorX12Direct extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;

    /**
     * If "Allow Encounter Claims" is enabled, this allows the claims to use
     * the alternate payor ID on the claim and sets the claims to report,
     * not chargeable. ie: RP = reporting, CH = chargeable
     *
     * @var bool|mixed
     */
    protected $encounter_claim = false;

    protected $x12_partner_batches = [];

    public function __construct($action, $encounter_claim = false)
    {
        parent::__construct($action);
        $this->encounter_claim = $encounter_claim;
    }

    /**
     * In the direct-billing setup method, we need to make sure that
     * the directories are created for our x-12 partners because
     * we save one batch file for each z-12 partner.
     *
     * We also set up a BillingClaimBatch for each x-12 partner in case
     * we have any claims to write to them in this group of claims.
     *
     * @param $context
     */
    public function setup(array $context)
    {
        // We have to prepare our batches here
        // Get all of our x-12 partners and make sure we have
        // directories to write to for them
        $result = sqlStatement("SELECT * from x12_partners");
        while ($row = sqlFetchArray($result)) {
            $has_dir = true;
            if (!isset($row['x12_sftp_local_dir'])) {
                // Local Directory not set
                $has_dir = false;
                $this->printToScreen(xl("No directory for X12 partner " . $row['name']));
            } else if (isset($row['x12_sftp_local_dir']) &&
                !is_dir($row['x12_sftp_local_dir'])) {
                // If the local directory doesn't exist, attempt to create it
                $has_dir = mkdir($row['x12_sftp_local_dir'], '644', true);
                if (false === $has_dir) {
                    $this->printToScreen(xl("Could not create directory for X12 partner " . $row['name']));
                }
            }

            $batch = new BillingClaimBatch();
            $filename = $batch->getBatFilename();
            $filename = str_replace('batch', 'batch-p'.$row['id'], $filename);
            $batch->setBatFilename($filename);

            // Only set the batch file directory if we have a valid directory
            if ($has_dir) {
                $batch->setBatFiledir($row['x12_sftp_local_dir']);
            }

            // Store the directory in an associative array with the partner ID as the index
            $this->x12_partner_batches[$row['id']] = $batch;

            // Look through the claims and set is_last on each one that
            // is the last for this x-12 partner
            foreach ($context['claims'] as $claim) {
                if ($claim->getPartner() === $row['id']) {
                    $lastClaim = $claim;
                }
            }
            $lastClaim->setIsLast(true);
        }
    }

    public function execute(BillingClaim $claim)
    {
        // If we are doing final billing (normal) or validate and mark-as-billed,
        // Then set up a new version
        $return = true;
        if ($this->getAction() === BillingProcessor::NORMAL ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {

            // This is a validation pass, but mark as billed if we're 'clearing'
            if ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $return = BillingUtilities::updateClaim(true, $claim->getPid(), $claim->getEncounter(), $claim->getPayorId(), $claim->getPayorType(), BillingClaim::STATUS_MARK_AS_BILLED);
            }

            // Do we really need to create another new version? Not sure exactly how this interacts
            // with the rest of the system
            $return = BillingUtilities::updateClaim(
                true,
                $claim->getPid(),
                $claim->getEncounter(),
                $claim->getPayorId(),
                $claim->getPayorType(),
                BillingClaim::STATUS_MARK_AS_BILLED,
                BillingClaim::BILL_PROCESS_IN_PROGRESS, // bill_process == 1 means??
                '', // process_file
                $claim->getTarget(),
                $claim->getPartner()
            );
        }

        // Get the correct batch file using the X-12 partner ID
        $batch = $this->x12_partner_batches[$claim->getPartner()];

        // Tell our batch that we've processed this claim
        $batch->addClaim($claim);

        // Use the tr3 format to output for direct-submission to insurance companies
        $log = '';
        $is_last_claim = $claim->getIsLast();
        $segs = explode("~\n", X125010837P::gen_x12_837_tr3($claim->getPid(), $claim->getEncounter(), $log, $this->encounter_claim, $is_last_claim));
        $this->appendToLog($log);
        $batch->append_claim($segs);

        // If we're validating only, exit. Otherwise finish the claim
        if ($this->getAction() === BillingProcessor::VALIDATE_ONLY) {
            // Don't finalize the claim, just return after we write the claim to the batch file
            // Do we need to do validate_payor_reset thing? It doesn't seem like it, maybe has to do with
            // secondary insurance?
            //validate_payer_reset($payer_id_held, $patient_id, $encounter);
            return $return;
        } else {
            // After we save the claim, update it with the filename (don't create a new revision)
            if (!BillingUtilities::updateClaim(false, $claim->getPid(), $claim->getEncounter(), -1, -1, 2, 2, $batch->getBatFilename())) {
                $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
            }
        }
    }

    public function complete(array $context)
    {
        $format_bat = "";
        $created_batches = [];
        // Loop through all of the X12 batch files we've created, one per x-12 partner,
        // and depending on the action we're running, either write the final claim
        // to disk, or format the content for printing to the screen.
        foreach ($this->x12_partner_batches as $x12_partner_id => $x12_partner_batch) {

            if (empty($x12_partner_batch->getBatContent())) {
                // If we didn't write any claims for this X12 partner
                // don't append the closing lines or write the claim file or do anything else
                continue;
            }

            $x12_partner_batch->append_claim_close();

            // If this is the final, validated claim, write to the edi location
            // for this x12 partner
            if ($this->getAction() === BillingProcessor::VALIDATE_ONLY ||
                $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $format_bat .= str_replace('~', PHP_EOL, $x12_partner_batch->getBatContent()) . "\n";
            } else if ($this->getAction() === BillingProcessor::NORMAL) {
                $x12_partner_batch->write_batch_file($x12_partner_id);
            }

            $created_batches[$x12_partner_id]= $x12_partner_batch;
        }

        // if validating (sending to screen for user)
        if ($this->getAction() === BillingProcessor::VALIDATE_ONLY ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
            $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
            echo $wrap;
            exit();
        } else if ($this->getAction() === BillingProcessor::NORMAL) {

            // In the "normal" operation, we have written the batch files to disk above, and
            // need to build a presentation for the user to download them.
            $html = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'>";

            // If the global is enabled to SFTP claim files, tell the user
            if ($GLOBALS['auto_sftp_claims_to_x12_partner']) {
                $html .= "<div class='alert alert-primary' role='alert'>" . xl("Sending Claims via STFP. Check status on the `Claim File Tracker`") . "</div>";
            }

            // Build the download URLs for our claim files so we can present them to the
            // user for download.
            $html .= "<ul class='list-group'>";
            foreach ($created_batches as $x12_partner_id => $created_batch) {
                $file = $created_batch->getBatFilename();
                $url = $GLOBALS['webroot'] . '/interface/billing/get_claim_file.php?key=' . $file .
                    '&partner=' . $x12_partner_id .
                    '&csrf_token_form=' . CsrfUtils::collectCsrfToken();
                $html .= "<li class='list-group-item d-flex justify-content-between align-items-center'><a href='$url'>$file</a></li>";
            }
            $html .= "</ul>";
            $html .= "</div></body></html>";

            // The logger gets is accessible in the billing_process.php page with the results.
            // We want all the good formatting that comes with the billing_process.php page (at the bottom)
            // but we don't want to show the close button because the modal already has that.
            $this->logger->setShowCloseButton(false);
            echo $html;
        }

    }
}
