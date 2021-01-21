<?php


namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;

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
