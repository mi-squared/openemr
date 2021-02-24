<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\ProcedurePerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class ProcedurePerformed extends QDMBaseType
{

    public $authorDatetime = null;

    public $relevantDatetime = null;

    public $relevantPeriod = null;

    public $reason = null;

    public $method = null;

    public $result = null;

    public $status = null;

    public $anatomicalLocationSite = null;

    public $rank = null;

    public $priority = null;

    public $incisionDatetime = null;

    public $negationRationale = null;

    public $components = [
        
    ];

    public $performer = null;

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.67';

    public $qrdaOid = '';

    public $qdmCategory = 'procedure';

    public $qdmStatus = 'performed';


}

