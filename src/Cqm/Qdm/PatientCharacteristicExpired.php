<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\PatientCharacteristicExpired
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class PatientCharacteristicExpired extends QDMBaseType
{

    /**
     * @property System.DateTime $expiredDatetime
     */
    public $expiredDatetime = null;

    /**
     * @property System.Code $cause
     */
    public $cause = null;

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Patient Characteristic Expired';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.57';

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
    public $qdmStatus = 'expired';

    public $_type = 'QDM::PatientCharacteristicExpired';


}

