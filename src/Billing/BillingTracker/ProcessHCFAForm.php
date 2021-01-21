<?php


namespace OpenEMR\Billing\BillingTracker;


use OpenEMR\Billing\BillingTracker\Traits\WritesToBillingLog;

class ProcessHCFAForm extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;


    public function setup(array $context)
    {
        // TODO: Implement setup() method.
    }

    public function execute(BillingClaim $claim)
    {
        // TODO: Implement execute() method.
    }

    public function complete(array $context)
    {
        // TODO: Implement complete() method.
    }
}
