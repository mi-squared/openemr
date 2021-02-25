<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\ProcedureOrder
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class ProcedureOrder extends QDMBaseType
{

    /**
     * @property System.DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property System.Code $reason
     */
    public $reason = null;

    /**
     * @property System.Code $anatomicalLocationSite
     */
    public $anatomicalLocationSite = null;

    /**
     * @property System.Integer $rank
     */
    public $rank = null;

    /**
     * @property System.Code $priority
     */
    public $priority = null;

    /**
     * @property System.Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property System.Any $requester
     */
    public $requester = null;

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.66';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'procedure';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'order';

    public $_type = 'QDM::ProcedureOrder';


}

