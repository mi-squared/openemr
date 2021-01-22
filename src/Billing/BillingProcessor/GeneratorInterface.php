<?php

namespace OpenEMR\Billing\BillingProcessor;

interface GeneratorInterface extends ProcessingTaskInterface
{
    public function setAction($action);

    public function generate(BillingClaim $claim);

    public function completeToFile(array $context);
}
