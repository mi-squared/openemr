<?php


namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\GeneratorCanValidateInterface;
use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;

class ProcessHCFAForm extends AbstractGenerator implements GeneratorInterface, GeneratorCanValidateInterface, LoggerInterface
{
    use WritesToBillingLog;

    public function setup(array $context)
    {
        // TODO: Implement setup() method.
    }

    public function validateOnly(BillingClaim $claim)
    {
        // TODO: Implement validateOnly() method.
    }

    public function validateAndClear(BillingClaim $claim)
    {
        // TODO: Implement validateAndClear() method.
    }

    public function generate(BillingClaim $claim)
    {
        // TODO: Implement generate() method.
    }

    public function completeToScreen(array $context)
    {
        // TODO: Implement completeToScreen() method.
    }

    public function completeToFile(array $context)
    {
        // TODO: Implement completeToFile() method.
    }


}
