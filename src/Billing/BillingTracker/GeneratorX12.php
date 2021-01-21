<?php


namespace OpenEMR\Billing\BillingTracker;


use OpenEMR\Billing\BillingTracker\Traits\GeneratesTxt;
use OpenEMR\Billing\BillingTracker\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\X125010837P;

class GeneratorX12 extends AbstractGenerator implements GeneratorInterface, LoggerInterface
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

    /**
     * @var BillingClaimBatch
     */
    protected $batch;

    public function __construct($action, $encounter_claim = false)
    {
        parent::__construct($action);
        $this->encounter_claim = $encounter_claim;
    }

    public function setup(array $context)
    {
        $this->batch = new BillingClaimBatch('.txt');
    }

    /**
     * @param BillingClaim $claim
     * @param bool $createNewVersion
     */
    public function execute(BillingClaim $claim)
    {
        // If we are doing final billing (normal) or validate and mark-as-billed,
        // Then set up a new version
        if ($this->getAction() === BillingProcessor::NORMAL ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {

            // This is a validation pass, but mark as billed if we're 'clearing'
            if ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $tmp = BillingUtilities::updateClaim(true, $claim->getPid(), $claim->getEncounter(), $claim->getPayorId(), $claim->getPayorType(), BillingClaim::STATUS_MARK_AS_BILLED);
            }

            // Do we really need to create another new version? Not sure exactly how this interacts
            // with the rest of the system
            $tmp = BillingUtilities::updateClaim(
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

        // Generate the file
        $log = '';
        $segs = explode("~\n", X125010837P::genX12837P($claim->getPid(), $claim->getEncounter(), $log, $this->encounter_claim));
        $this->appendToLog($log);
        $this->batch->append_claim($segs);

        // Store the claims that are in this claims batch, because
        // if remote SFTP is enabled, we'll need the x12 partner ID to look up SFTP credentials
        $this->batch->addClaim($claim);

        // If we're validating only, exit. Otherwise finish the claim
        if ($this->getAction() === BillingProcessor::VALIDATE_ONLY ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
            // Don't finalize the claim, just return after we write the claim to the batch file
            // TODO Do we need to do payer reset thing??
            //validate_payer_reset($payer_id_held, $patient_id, $encounter);
            return $tmp;
        } else {

            // After we save the claim, update it with the filename (don't create a new revision)
            if (!BillingUtilities::updateClaim(false, $claim->getPid(), $claim->getEncounter(), -1, -1, 2, 2, $this->batch->getBatFilename())) {
                $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
            }
        }

        return $tmp;
    }

    public function complete(array $context)
    {
        $this->batch->append_claim_close();
        // If we're validating only, or clearing and validating, don't write to our EDI directory
        // Just send to the browser in that case for the end-user to review.
        if ($this->getAction() === BillingProcessor::VALIDATE_ONLY ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
            $format_bat = str_replace('~', PHP_EOL, $this->batch->getBatContent());
            $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
            echo $wrap;
            exit();
        } else if ($this->getAction() === BillingProcessor::NORMAL) {
            $success = $this->batch->write_batch_file();
            if ($success) {
                $this->printToScreen(xl('X-12 Generated Successfully'));
            } else {
                $this->printToScreen(xl('Error Generating Batch File'));
            }
        }
    }
}
