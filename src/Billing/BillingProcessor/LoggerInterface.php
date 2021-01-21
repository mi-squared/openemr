<?php


namespace OpenEMR\Billing\BillingProcessor;


interface LoggerInterface
{
    public function getLogger();

    public function setLogger(BillingLogger $logger);

    public function printToScreen($message);

    public function appendToLog($message);
}
