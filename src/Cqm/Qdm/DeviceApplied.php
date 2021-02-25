<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\DeviceApplied
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class DeviceApplied extends QDMBaseType
{

    /**
     * @property System.DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property System.DateTime $relevantDatetime
     */
    public $relevantDatetime = null;

    /**
     * @property interval<System.DateTime> $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property System.Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property System.Code $reason
     */
    public $reason = null;

    /**
     * @property System.Code $anatomicalLocationSite
     */
    public $anatomicalLocationSite = null;

    /**
     * @property System.Any $performer
     */
    public $performer = null;

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.13';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'device';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'applied';

    public $_type = 'QDM::DeviceApplied';


}

