<?php


namespace OpenEMR\Billing\BillingTracker;


use OpenEMR\Billing\BillingTracker\Traits\WritesToBillingLog;

class TaskMarkAsClear extends AbstractProcessingTask implements ProcessingTaskInterface, LoggerInterface
{
    use WritesToBillingLog;

    public function setup(array $context)
    {
        // nothing to do
    }

    public function execute(BillingClaim $claim)
    {
        $this->appendToScreen(xl("Claim ") . $claim->getId() . xl(" was marked as billed only.") . "\n");
        return $this->clearClaim($claim);
    }

    public function complete(array $context)
    {
        // nothing to do
    }
}
