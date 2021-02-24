<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\MedicationActive
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class MedicationActive extends QDMBaseType
{

    public $relevantDatetime = null;

    public $relevantPeriod = null;

    public $dosage = null;

    public $frequency = null;

    public $route = null;

    public $recorder = null;

    public $qdmTitle = 'Medication, Active';

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.44';

    public $qrdaOid = '';

    public $qdmCategory = 'medication';

    public $qdmStatus = 'active';


}

