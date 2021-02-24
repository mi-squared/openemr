<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\ImmunizationAdministered
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class ImmunizationAdministered extends QDMBaseType
{

    public $authorDatetime = null;

    public $relevantDatetime = null;

    public $reason = null;

    public $dosage = null;

    public $route = null;

    public $negationRationale = null;

    public $performer = null;

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.112';

    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.140';

    public $qdmCategory = 'immunization';

    public $qdmStatus = 'administered';


}

