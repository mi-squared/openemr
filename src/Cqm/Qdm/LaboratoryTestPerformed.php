<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\LaboratoryTestPerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class LaboratoryTestPerformed extends QDMBaseType
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
     * @property System.Code $status
     */
    public $status = null;

    /**
     * @property System.Code $method
     */
    public $method = null;

    /**
     * @property System.Any $result
     */
    public $result = null;

    /**
     * @property System.DateTime $resultDatetime
     */
    public $resultDatetime = null;

    /**
     * @property System.Code $reason
     */
    public $reason = null;

    /**
     * @property interval<System.Quantity> $referenceRange
     */
    public $referenceRange = null;

    /**
     * @property System.Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property list<QDM.ResultComponent> $components
     */
    public $components = [
        
    ];

    /**
     * @property System.Any $performer
     */
    public $performer = null;

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.42';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'laboratory_test';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'performed';

    public $_type = 'QDM::LaboratoryTestPerformed';


}

