<?php

namespace OpenEMR\Billing\BillingProcessor;

interface GeneratorCanValidateInterface
{
    public function validateOnly(BillingClaim $claim);

    public function validateAndClear(BillingClaim $claim);

    public function completeToScreen(array $context);
}
