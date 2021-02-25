<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\ImmunizationAdministered
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class ImmunizationAdministered extends QDMBaseType
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
     * @property System.Code $reason
     */
    public $reason = null;

    /**
     * @property System.Quantity $dosage
     */
    public $dosage = null;

    /**
     * @property System.Code $route
     */
    public $route = null;

    /**
     * @property System.Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property System.Any $performer
     */
    public $performer = null;

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.112';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.140';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'immunization';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'administered';

    public $_type = 'QDM::ImmunizationAdministered';


}

