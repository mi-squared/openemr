<?php


namespace OpenEMR\Services\Qdm;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Services\ConditionService as BaseService;
use OpenEMR\Services\Qdm\Interfaces\MakesQdmModelInterface;

class ConditionService extends BaseService implements MakesQdmModelInterface
{
    public function fetchAllByPid($pid)
    {
        $result = sqlQuery("SELECT uuid from patient_data where pid = ?", [$pid]);
        $uuid = UuidRegistry::uuidToString($result['uuid']);
        $processingResult = $this->getAll(['lists.pid' => $uuid]);
        $records = $processingResult->getData();
        return $records;
    }

    /**
     * Map an OpenEMR record into a QDM model
     *
     * This
     *
     * @param array $record
     * @return Diagnosis|null
     * @throws \Exception
     */
    public function makeQdmModel(array $record)
    {
        $qdmRecord = null;
        if ($record['type'] === 'medical_problem') {
            if ($record['diagnosis']) {
                // Get diagnosis
                $qdmRecord = new Diagnosis([
                    'prevalencePeriod' => new Interval([
                        'low' => $record['begdate'],
                        'high' => $record['enddate'],
                        'lowClosed' => $record['begdate'] ? true : false,
                        'highClosed' => $record['enddate'] ? true : false]),
                    'dataElementCodes' => $record['diagnosis'] // diagnosis contains code and system
                ]);
            }
        }

        return $qdmRecord;
    }
}
