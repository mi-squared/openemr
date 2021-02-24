<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\Patient
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class Patient extends \OpenEMR\Cqm\Qdm\BaseTypes\Any
{

    use Traits\PatientExtension;

    public $birthDatetime = null;

    public $qdmVersion = '5.5';


}

