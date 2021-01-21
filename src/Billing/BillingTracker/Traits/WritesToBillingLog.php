<?php


namespace OpenEMR\Billing\BillingTracker\Traits;

use OpenEMR\Billing\BillingTracker\BillingLogger;

trait WritesToBillingLog
{
    protected $logger;

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(BillingLogger $logger)
    {
        $this->logger = $logger;
    }

    public function printToScreen($message)
    {
        $this->logger->printToScreen($message);
    }

    public function appendToLog($message)
    {
        $this->logger->appendToLog($message);
    }
}
