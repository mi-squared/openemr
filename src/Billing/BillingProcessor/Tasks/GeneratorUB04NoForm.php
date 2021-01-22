<?php

namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\GeneratorInterface;
use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Traits\WritesToBillingLog;
use OpenEMR\Billing\BillingUtilities;

require_once __DIR__ . '/../../../interface/billing/ub04_dispose.php';

class GeneratorUB04NoForm extends AbstractGenerator implements GeneratorInterface, LoggerInterface
{
    use WritesToBillingLog;

    // These two are specific to UB04
    protected $template = array();
    protected $ub04id = array();
    protected $batch;

    public function setup(array $context)
    {
        $this->batch = new BillingClaimBatch('.pdf');

        // This was called at top of old billing_process.php so call in setup()
        ub04_dispose();
    }

    public function execute(BillingClaim $claim)
    {
        $log = "";
        $this->template[] = buildTemplate($claim->getPid(), $claim->getEncounter(), "", "", $log);
        $this->appendToLog($log);

        if (!BillingUtilities::updateClaim(false, $claim->getPid(), $claim->getEncounter(), -1, -1, 2, 2, $this->batch->getBatFilename(), 'ub04', -1, 0, json_encode($this->ub04id))) {
            $this->printToScreen(xl("Internal error: claim ") . $claim->getId() . xl(" not found!") . "\n");
        }
    }

    public function complete(array $context)
    {
        ub04Dispose('download', $this->template, $this->batch->getBatFilename(), 'noform');
        exit();
    }
}
