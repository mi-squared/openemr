<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\Symptom
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class Symptom extends QDMBaseType
{

    /**
     * @property interval<System.DateTime> $prevalencePeriod
     */
    public $prevalencePeriod = null;

    /**
     * @property System.Code $severity
     */
    public $severity = null;

    /**
     * @property System.Any $recorder
     */
    public $recorder = null;

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Symptom';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.116';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.136';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'symptom';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = '';

    public $_type = 'QDM::Symptom';


}

