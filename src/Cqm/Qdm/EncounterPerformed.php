<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\EncounterPerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class EncounterPerformed extends QDMBaseType
{

    public $authorDatetime = null;

    public $admissionSource = null;

    public $relevantPeriod = null;

    public $dischargeDisposition = null;

    public $facilityLocations = [
        
    ];

    public $diagnoses = [
        
    ];

    public $negationRationale = null;

    public $lengthOfStay = null;

    public $priority = null;

    public $participant = null;

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.5';

    public $qrdaOid = '';

    public $qdmCategory = 'encounter';

    public $qdmStatus = 'performed';


}

