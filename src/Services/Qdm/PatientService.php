<?php


namespace OpenEMR\Services\Qdm;

use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\PatientService as BaseService;

class PatientService extends BaseService
{
    public function makePatient($pid)
    {
        $patient = $this->findByPid($pid);
        $qdmPatient = new Patient([
            'birthDatetime' => $patient['DOB'],
            'bundleId' => 1,
            '_fullName' => $patient['fname'] . ' ' . $patient['lname'],
            '_openEmrPid' => $patient['pid']
        ]);

        $conditionService = new ConditionService();
        $conditions = $conditionService->fetchAllByPid($pid);
        foreach ($conditions as $condition) {
            $qdmCondition = $conditionService->makeQdmRecord($condition);
            $qdmPatient->add_data_element($qdmCondition);
        }

        return $qdmPatient;

    }
}
