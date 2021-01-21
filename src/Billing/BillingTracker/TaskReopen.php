<?php


namespace OpenEMR\Billing\BillingTracker;


use OpenEMR\Billing\BillingTracker\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;

class TaskReopen extends AbstractProcessingTask implements ProcessingTaskInterface, LoggerInterface
{
    use WritesToBillingLog;

    public function setup(array $context)
    {
        // nothing to do
    }

    public function execute(BillingClaim $claim)
    {
        $this->printToScreen("Opening claim");
        $tmp = BillingUtilities::updateClaim(
            true,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            1,
            0 // Set 'billed' flag to '0' to re-open claim
        );
        return $tmp;
    }

    public function complete(array $context)
    {
        // nothing to do
    }
}
