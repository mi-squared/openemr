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
use OpenEMR\Billing\BillingProcessor\GeneratorCanValidateInterface;
use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\X125010837P;
use OpenEMR\Common\Csrf\CsrfUtils;

class GeneratorX12Direct extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface
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

    protected $x12_partners = [];

    public function __construct($action, $encounter_claim = false)
    {
        parent::__construct($action);
        $this->encounter_claim = $encounter_claim;
    }

    protected function updateBatchFile(BillingClaim $claim)
    {
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

        return $batch;
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

            // Store the x-12 partner's data in case we need to reference it (like need the Name or something)
            $this->x12_partners[$row['id']] = $row;

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

    public function validateOnly(BillingClaim $claim)
    {
        $this->updateBatchFile($claim);
    }

    public function validateAndClear(BillingClaim $claim)
    {
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

        // Return the batch we updated (depending on x-12 partner)
        return $this->updateBatchFile($claim);
    }

    public function generate(BillingClaim $claim)
    {
        // If we are doing final billing (normal) or validate and mark-as-billed,
        // Use the claim to update the appropriate batch file (depends on x-12 partner)
        // and return the batch we updated
        $batch = $this->validateAndClear($claim);

        if (!BillingUtilities::updateClaim(false, $claim->getPid(), $claim->getEncounter(), -1, -1, 2, 2, $batch->getBatFilename())) {
            $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
        }
    }

    /**
     * This is the common finish function to both completeToFile (normal)
     * and completeToScreen (validation). We pass the callback to let the
     * caller specify what we do after we finish up.
     *
     * This uses the generator's 'action' attribute to decide whether
     * to generate the edi file or not. If we're in NORMAL mode, generate the
     * file.
     *
     * @param array $context
     * @param callable $callback
     */
    protected function finish(array $context, callable $callback)
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

            // Write the batch content to formatted string for presenting to user
            $format_bat .= str_replace('~', PHP_EOL, $x12_partner_batch->getBatContent()) . "\n";

            // Store all the batches we create with the x12-partner ID as index
            // so we can pass them to the callback
            $created_batches[$x12_partner_id]= $x12_partner_batch;
        }

        // Call the callback with new context
        $callback([
            'created_batches' => $created_batches,
            'format_bat' => $format_bat
        ]);
    }

    /**
     * Complete the file and write formatted content to the edi directory.
     *
     * When running 'normal' action, this method is called
     * by AbstractGenerator's complete() method.
     *
     * We call finish with a closure that
     *
     * @param array $context
     */
    public function completeToFile(array $context)
    {
        $this->finish($context, function ($context)   {

            // Get the created_batches from the finish method
            $created_batches = $context['created_batches'];

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
                // This is the final, validated claim, write to the edi location for this x12 partner
                $created_batch->write_batch_file($x12_partner_id);
                $x12_partner_name = text($this->x12_partners[$x12_partner_id]['name']);
                // For the modal, build a list of downloads
                $file = $created_batch->getBatFilename();
                $url = $GLOBALS['webroot'] . '/interface/billing/get_claim_file.php?key=' . $file .
                    '&partner=' . $x12_partner_id .
                    '&csrf_token_form=' . CsrfUtils::collectCsrfToken();
                $html .=
                    "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        <a href='$url'>$file</a>
                        <span class='badge badge-primary badge-pill'>$x12_partner_name</span>
                    </li>";
            }
            $html .= "</ul>";
            $html .= "</div></body></html>";

            echo $html;
        });
    }

    public function completeToScreen(array $context)
    {
        $this->finish($context, function ($context) {

            // Get the format_bat string from the finish method
            $format_bat = $context['format_bat'];

            // if validating (sending to screen for user)
            $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
            echo $wrap;
            exit();
        });
    }
}
