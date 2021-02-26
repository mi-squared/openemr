<?php


namespace OpenEMR\Services\Qdm;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\EncounterPerformed;
use OpenEMR\Services\EncounterService as BaseService;
use OpenEMR\Services\Qdm\Interfaces\MakesQdmModelInterface;

class EncounterService extends BaseService implements MakesQdmModelInterface
{
    public function fetchAllByPid($pid)
    {
        $result = sqlQuery("SELECT uuid from patient_data where pid = ?", [$pid]);
        $uuid = UuidRegistry::uuidToString($result['uuid']);
        $processingResult = $this->getEncountersBySearch(['pid' => $uuid]);
        $records = $processingResult->getData();
        return $records;
    }


    public function makeQdmModel(array $record)
    {
       $qdmRecord = new EncounterPerformed([
           'id' => $record['uuid'],
            'relevantPeriod' => new Interval([
                'low' => $record['date'],
                'high' => $record['date'],
                'lowClosed' => $record['date'] ? true : false,
                'highClosed' => $record['date'] ? true : false
            ])
       ]);

       return $qdmRecord;
    }
}
