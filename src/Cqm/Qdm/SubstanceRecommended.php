<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\SubstanceRecommended
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class SubstanceRecommended extends QDMBaseType
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
     * @property System.Quantity $dosage
     */
    public $dosage = null;

    /**
     * @property System.Code $frequency
     */
    public $frequency = null;

    /**
     * @property System.Integer $refills
     */
    public $refills = null;

    /**
     * @property System.Code $route
     */
    public $route = null;

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
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.78';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'substance';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'recommended';

    public $_type = 'QDM::SubstanceRecommended';


}

