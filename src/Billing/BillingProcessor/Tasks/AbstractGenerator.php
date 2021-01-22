<?php


namespace OpenEMR\Billing\BillingProcessor\Tasks;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingProcessor;
use OpenEMR\Billing\BillingProcessor\GeneratorCanValidateInterface;
use OpenEMR\Common\Csrf\CsrfUtils;

abstract class AbstractGenerator extends AbstractProcessingTask
{
    protected $action;

    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * This abstract class for generators implements the execute method
     * so we can further hone exactly which operation we want to run.
     *
     * This helps reduce conditional statements in the generator classes
     * by checking the action here and calling the appropriate method.
     *
     * If needed the individual generator can override this method and
     * take control of the entire execute() process.
     *
     * @param BillingClaim $claim
     */
    public function execute(BillingClaim $claim)
    {
        if ($this instanceof GeneratorCanValidateInterface) {
            if ($this->getAction() === BillingProcessor::VALIDATE_ONLY) {
                $this->validateOnly($claim);
            } else if ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $this->validateAndClear($claim);
            }
        }

        if ($this->getAction() === BillingProcessor::NORMAL) {
            $this->generate($claim);
        }
    }

    public function complete(array $context)
    {
        if ($this instanceof GeneratorCanValidateInterface) {
            if ($this->getAction() === BillingProcessor::VALIDATE_ONLY) {
                $this->completeToScreen($context);
            } else if ($this->getAction() === BillingProcessor::VALIDATE_AND_CLEAR) {
                $this->completeToScreen($context);
            }
        }

        if ($this->getAction() === BillingProcessor::NORMAL) {
            $this->completeToFile($context);
        }
    }

    /**
     * This is a helper function for generators that produce a file
     * as output, and need to initiate a file download for the
     * user. This prints javascript that will call the get_claim_file.php
     * endpoint and initiate the download.
     *
     * @param $filename
     * @param $location
     * @param false $delete
     */
    public function printDownloadClaimFileJS($filename, $location = '', $delete = false)
    {
        $url = $GLOBALS['webroot'] . '/interface/billing/get_claim_file.php?key=' . $filename .
            '&location=' . $location .
            '&delete=' . $delete .
            '&csrf_token_form=' . CsrfUtils::collectCsrfToken();
        echo "<script type='text/JavaScript'>window.location = '$url'</script>";
    }
}
