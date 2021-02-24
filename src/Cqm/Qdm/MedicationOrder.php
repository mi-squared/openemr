<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\MedicationOrder
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class MedicationOrder extends QDMBaseType
{

    public $authorDatetime = null;

    public $relevantPeriod = null;

    public $refills = null;

    public $dosage = null;

    public $supply = null;

    public $frequency = null;

    public $daysSupplied = null;

    public $route = null;

    public $setting = null;

    public $reason = null;

    public $negationRationale = null;

    public $prescriber = null;

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.51';

    public $qrdaOid = '';

    public $qdmCategory = 'medication';

    public $qdmStatus = 'order';


}

