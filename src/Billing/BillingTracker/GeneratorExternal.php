<?php


namespace OpenEMR\Billing\BillingTracker;


use OpenEMR\Billing\BillingTracker\Traits\WritesToBillingLog;

class GeneratorExternal extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;

    protected $be;

    public function setup($context = null)
    {
        $this->be = new \BillingExport();
    }

    public function execute(BillingClaim $claim)
    {
        $this->be->addClaim($claim->getPid(), $claim->getEncounter());
        return $this->clearClaim($claim);
    }

    public function complete($context = null)
    {
        // TODO: Implement complete() method.
    }
}
