<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\CareGoal
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class CareGoal extends QDMBaseType
{

    /**
     * @property System.Date $statusDate
     */
    public $statusDate = null;

    /**
     * @property interval<System.DateTime> $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property list<System.String> $relatedTo
     */
    public $relatedTo = [
        
    ];

    /**
     * @property System.Any $targetOutcome
     */
    public $targetOutcome = null;

    /**
     * @property System.Any $performer
     */
    public $performer = null;

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Care Goal';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.7';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'care_goal';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = '';

    public $_type = 'QDM::CareGoal';


}

