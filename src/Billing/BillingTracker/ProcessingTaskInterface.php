<?php

namespace OpenEMR\Billing\BillingTracker;

interface ProcessingTaskInterface
{
    public function setup(array $context);

    public function execute(BillingClaim $claim);

    public function complete(array $context);
}
