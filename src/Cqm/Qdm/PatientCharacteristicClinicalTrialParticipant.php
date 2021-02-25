<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\PatientCharacteristicClinicalTrialParticipant
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class PatientCharacteristicClinicalTrialParticipant extends QDMBaseType
{

    /**
     * @property System.Code $reason
     */
    public $reason = null;

    /**
     * @property interval<System.DateTime> $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Patient Characteristic Clinical Trial Participant';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.6';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.51';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'patient_characteristic';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'clinical_trial_participant';

    public $_type = 'QDM::PatientCharacteristicClinicalTrialParticipant';


}

