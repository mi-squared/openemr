<?php


namespace OpenEMR\Billing\BillingTracker;

use OpenEMR\Billing\BillingTracker\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\X125010837I;

require_once __DIR__ . '/../../../interface/billing/ub04_dispose.php';

class GeneratorUB04X12 extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;

    // These two are specific to UB04
    protected $template = array();
    protected $ub04id = array();

    protected $batch;

    public function setup(array $context)
    {
        $this->batch = new BillingClaimBatch('.txt');
    }

    public function execute(BillingClaim $claim)
    {
        // If we are doing final billing (normal) or validate and mark-as-billed,
        // Then set up a new version
        if ($this->getAction() === BillingProcessor::NORMAL ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {

            // This is a validation pass, but mark as billed if we're 'clearing'
            if ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $tmp = BillingUtilities::updateClaim(
                    true,
                    $claim->getPid(),
                    $claim->getEncounter(),
                    $claim->getPayorId(),
                    $claim->getPayorType(),
                    BillingClaim::STATUS_MARK_AS_BILLED
                );
            }

            $this->ub04id = get_ub04_array($claim->getPid(), $claim->getEncounter());
            $ub_save = json_encode($this->ub04id);
            $tmp = BillingUtilities::updateClaim(
                true,
                $claim->getPid(),
                $claim->getEncounter(),
                $claim->getPayorId(),
                $claim->getPayorType(),
                BillingClaim::STATUS_MARK_AS_BILLED,
                BillingClaim::BILL_PROCESS_IN_PROGRESS,
                '',
                $claim->getTarget(),
                $claim->getPartner() . '-837I',
                0,
                $ub_save
            );
        }

        // Do the UB04 processing
        $log = '';
        $segs = explode("~\n", X125010837I::generateX12837I($claim->getPid(), $claim->getEncounter(), $log, $this->ub04id));
        $this->appendToLog($log);
        $this->batch->append_claim($segs);

        // Store the claims that are in this claims batch, because
        // if remote SFTP is enabled, we'll need the x12 partner ID to look up SFTP credentials
        $this->batch->addClaim($claim);

        if ($this->getAction() === BillingProcessor::VALIDATE_ONLY ||
            $this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
            // Do we need to do the payor reset thing???
            return $tmp;
        } else if ($this->getAction() == BillingProcessor::NORMAL) {
            $tmp = BillingUtilities::updateClaim(
                false,
                $claim->getPid(),
                $claim->getEncounter(),
                -1,
                -1,
                2,
                2,
                $this->batch->getBatFilename(),
                'X12-837I',
                -1,
                0,
                json_encode($this->ub04id)
            );

            // If we had an error, print to screen
            if (!$tmp) {
                $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
            }
        }

        return $tmp;
    }

    public function complete(array $context)
    {
        $this->batch->append_claim_close();

        if ($this->getAction() == BillingProcessor::VALIDATE_ONLY ||
            ($this->getAction() == BillingProcessor::VALIDATE_AND_CLEAR)) {
            $format_bat = str_replace('~', PHP_EOL, $this->batch->getBatContent());
            $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
            echo $wrap;
        } else if ($this->getAction() == BillingProcessor::NORMAL) {
            $success = $this->batch->write_batch_file();
            if ($success) {
                $this->printToScreen(xl('X-12 Generated Successfully'));
            } else {
                $this->printToScreen(xl('Error Generating Batch File'));
            }
        }
    }
}
