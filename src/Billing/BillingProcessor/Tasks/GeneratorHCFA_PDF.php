<?php

/**
 * This class represents the task that compiles claims into
 * a HCFA form batch. This prints the claim data only, with no
 * form fields that are present on the HCFA 1500 paper form.
 *
 * The other HCFA generator will print the data over an image of
 * the paper form fields.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
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
use OpenEMR\Billing\Hcfa1500;

class GeneratorHCFA_PDF extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;

    /**
     * Instance of the Cezpdf object for writing
     * @Cezpdf
     */
    protected $pdf;

    /**
     * Our billing claim batch for tracking the filename and other
     * generic claim batch things
     *
     * @BillingClaimBatch
     */
    protected $batch;

    /**
     * When we run the execute function on each claim, we don't want
     * to create a new page the first time. The instantiation of the PDF
     * object "comes with" a canvas to write to, so the first claim, we
     * don't need to create one. On subsequent claims, we do so we initialize
     * this to false, and then set to true after the first claim.
     *
     * @bool
     */
    protected $createNewPage;

    public function setup(array $context)
    {
        $post = $context['post'];
        $this->pdf = new \Cezpdf('LETTER');
        $this->pdf->ezSetMargins(trim($post['top_margin']) + 0, 0, trim($post['left_margin']) + 0, 0);
        $this->pdf->selectFont('Courier');

        // This is to tell our execute method not to create a new page the first claim
        $this->createNewPage = false;

        // Instantiate mainly for the filename creation, we're not tracking text segments
        // since we're generating a PDF, which is managed in this object
        $this->batch = new BillingClaimBatch('.pdf');
    }

    public function execute(BillingClaim $claim)
    {
        // If we are doing final billing (normal) or validate and mark-as-billed,
        // Then set up a new version
        if ($this->getAction() === BillingProcessor::NORMAL ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {

            // This is a validation pass
            if ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $tmp = BillingUtilities::updateClaim(true, $claim->getPid(), $claim->getEncounter(), $claim->getPayorId(), $claim->getPayorType(), 2);
            }

            // Do we really need to create another new version? Not sure exactly how this interacts
            // with the rest of the system
            $tmp = BillingUtilities::updateClaim(
                true,
                $claim->getPid(),
                $claim->getEncounter(),
                $claim->getPayorId(),
                $claim->getPayorType(),
                BillingClaim::STATUS_MARK_AS_BILLED, // status == 2 means
                BillingClaim::BILL_PROCESS_IN_PROGRESS, // bill_process == 1 means??
                '', // process_file
                'hcfa'
            );
        }

        // Do the actual claim processing
        $log = '';
        $hcfa = new Hcfa1500();
        $lines = $hcfa->genHcfa1500($claim->getPid(), $claim->getEncounter(), $log);
        $this->appendToLog($log);
        $alines = explode("\014", $lines); // form feeds may separate pages
        foreach ($alines as $tmplines) {
            // The first claim we don't create a new page.
            if ($this->createNewPage) {
                $this->pdf->ezNewPage();
            } else {
                $this->createNewPage = true;
            }
            $this->pdf->ezSetY($this->pdf->ez['pageHeight'] - $this->pdf->ez['topMargin']);
            $this->pdf->ezText($tmplines, 12, array(
                'justification' => 'left',
                'leading' => 12
            ));
        }

        // If we're just validating, do nothing, otherwise finalize the claim
        if ($this->getAction() === BillingProcessor::VALIDATE_ONLY ||
            ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR)) {
            $this->printToScreen(xl("Successfully Validated claim") . ": " . $claim->getId());
            //validate_payer_reset($payer_id_held, $patient_id, $encounter);
            return;
        } else if ($this->getAction() === BillingProcessor::NORMAL) {

            // Finalize the claim
            if (!BillingUtilities::updateClaim(false, $claim->getPid(), $claim->getEncounter(), -1, -1, 2, 2, $this->batch->getBatFilename())) {
                $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
            }

            $this->printToScreen(xl("Successfully processed claim") . ": " . $claim->getId());
        }
    }

    /**
     * Generate the download output
     *
     * @param array $context
     */
    public function complete(array $context)
    {
        if ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR ||
            $this->getAction() === BillingProcessor::VALIDATE_ONLY) {

            // If we are just validating, make a temp file
            $tmp_claim_file = $GLOBALS['temporary_files_dir'] .
                DIRECTORY_SEPARATOR .
                $this->batch->getBatFilename();
            file_put_contents($tmp_claim_file, $this->pdf->ezOutput());

            // If we are just validating, the output should be a PDF presented
            // to the user, but we don't save to the edi/ directory.
            // This just writes to a tmp file, serves to user and then removes tmp file
            $this->logger->setLogCompleteCallback($this, 'initiateTmpDownload');

        } else if ($this->getAction() === BillingProcessor::NORMAL) {

            // If a writable edi directory exists (and it should), write the pdf to it.
            $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/documents/edi/{$this->batch->getBatFilename()}", 'a');
            if ($fh) {
                fwrite($fh, $this->pdf->ezOutput());
                fclose($fh);
            }

            // Tell the billing_process.php script to initiate a download of this file
            // that's in the edi directory
            $this->logger->setLogCompleteCallback($this, 'initiateDownload');
        }
    }

    public function initiateDownload()
    {
        // This uses our parent's method to print the JS that automatically initiates
        // the download of this file, after the screen bill_log messages have printed
        $this->printDownloadClaimFileJS($this->batch->getBatFilename());
    }

    public function initiateTmpDownload()
    {
        // This uses our parent's method to print the JS that automatically initiates
        // the download of this file, after the screen bill_log messages have printed
        // Tell the get_claim_file.php file to delete the file after download because it's
        // just a temp claim file
        $this->printDownloadClaimFileJS($this->batch->getBatFilename(), 'tmp', true);
    }
}
