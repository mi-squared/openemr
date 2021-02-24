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

    public $expiredDatetime = null;

    public $cause = null;

    public $qdmTitle = 'Patient Characteristic Expired';

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.57';

    public $qrdaOid = '';

    public $qdmCategory = 'patient_characteristic';

    public $qdmStatus = 'expired';


}

