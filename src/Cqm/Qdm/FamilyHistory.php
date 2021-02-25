<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\FamilyHistory
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class FamilyHistory extends QDMBaseType
{

    /**
     * @property System.DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property System.Code $relationship
     */
    public $relationship = null;

    /**
     * @property System.Any $recorder
     */
    public $recorder = null;

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Family History';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.111';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.12';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'family_history';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = '';

    public $_type = 'QDM::FamilyHistory';


}

