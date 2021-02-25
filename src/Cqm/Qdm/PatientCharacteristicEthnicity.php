<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\PatientCharacteristicEthnicity
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class PatientCharacteristicEthnicity extends QDMBaseType
{

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Patient Characteristic Ethnicity';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.56';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'patient_characteristic';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'ethnicity';

    public $_type = 'QDM::PatientCharacteristicEthnicity';


}

