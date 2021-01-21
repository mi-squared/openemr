<?php

namespace OpenEMR\Billing\BillingProcessor;

interface GeneratorInterface extends ProcessingTaskInterface
{
    public function setAction($action);
}
