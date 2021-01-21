<?php


namespace OpenEMR\Billing\BillingTracker;

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
