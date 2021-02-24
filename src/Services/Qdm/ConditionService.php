<?php


namespace OpenEMR\Services\Qdm;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Services\ConditionService as BaseService;

class ConditionService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetchAllByPid($pid)
    {
        $result = sqlQuery("SELECT uuid from patient_data where pid = ?", [$pid]);
        $uuid = UuidRegistry::uuidToString($result['uuid']);
        $processingResult = $this->getAll(['lists.pid' => $uuid]);
        $conditions = $processingResult->getData();
        return $conditions;
    }

    public function makeQdmRecord(array $record)
    {
        $qdmRecord = null;
        if ($record['type'] === 'medical_problem') {
            $diagnosis = [];
            if ($record['diagnosis']) {
                // Get diagnosis
                $qdmRecord = new Diagnosis([
                    'prevalencePeriod' => new Interval(['low' => $record['begdate'], 'high' => $record['enddate'], 'lowClosed' => true, 'highClosed' => true]),
                    'dataElementCodes' => $record['diagnosis']
                ]);
            }
        }

        return $qdmRecord;
    }
}
