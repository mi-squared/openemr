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

    public $authorDatetime = null;

    public $category = null;

    public $medium = null;

    public $sender = null;

    public $recipient = null;

    public $relatedTo = [
        
    ];

    public $sentDatetime = null;

    public $receivedDatetime = null;

    public $negationRationale = null;

    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.132';

    public $qrdaOid = '';

    public $qdmCategory = 'communication';

    public $qdmStatus = 'performed';


}

