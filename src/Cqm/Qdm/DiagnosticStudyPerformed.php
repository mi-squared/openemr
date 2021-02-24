<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\DiagnosticStudyPerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class DiagnosticStudyPerformed extends QDMBaseType
{

    public $authorDatetime = null;

    public $relevantDatetime = null;

    public $relevantPeriod = null;

    public $reason = null;

    public $result = null;

    public $resultDatetime = null;

    public $status = null;

    public $method = null;

    public $facilityLocation = null;

    public $negationRationale = null;

    public $components = [
        
    ];

    public $performer = null;

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.23';

    public $qrdaOid = '';

    public $qdmCategory = 'diagnostic_study';

    public $qdmStatus = 'performed';


}

