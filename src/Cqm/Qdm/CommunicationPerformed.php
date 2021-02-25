<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\CommunicationPerformed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class CommunicationPerformed extends QDMBaseType
{

    /**
     * @property System.DateTime $authorDatetime
     */
    public $authorDatetime = null;

    /**
     * @property System.Code $category
     */
    public $category = null;

    /**
     * @property System.Code $medium
     */
    public $medium = null;

    /**
     * @property System.Any $sender
     */
    public $sender = null;

    /**
     * @property System.Any $recipient
     */
    public $recipient = null;

    /**
     * @property list<System.String> $relatedTo
     */
    public $relatedTo = [
        
    ];

    /**
     * @property System.DateTime $sentDatetime
     */
    public $sentDatetime = null;

    /**
     * @property System.DateTime $receivedDatetime
     */
    public $receivedDatetime = null;

    /**
     * @property System.Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.132';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'communication';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'performed';

    public $_type = 'QDM::CommunicationPerformed';


}

