<?php

namespace OpenEMR\Billing\BillingTracker;

interface GeneratorInterface extends ProcessingTaskInterface
{
    public function setAction($action);
}
