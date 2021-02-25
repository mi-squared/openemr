<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\AllergyIntolerance
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class AllergyIntolerance extends QDMBaseType
{

    /**
     * @property System.DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property interval<System.DateTime> $prevalencePeriod
     */
    public $prevalencePeriod = null;

    /**
     * @property System.Code $type
     */
    public $type = null;

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
    public $qdmTitle = 'Allergy/Intolerance';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.119';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'allergy';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'intolerance';

    public $_type = 'QDM::AllergyIntolerance';


}

