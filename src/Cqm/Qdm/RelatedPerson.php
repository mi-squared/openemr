<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\RelatedPerson
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class RelatedPerson extends QDMBaseType
{

    /**
     * @property QDM.Identifier $identifier
     */
    public $identifier = null;

    /**
     * @property System.String $linkedPatientId
     */
    public $linkedPatientId = null;

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Related Person';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.141';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'related_person';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = '';

    public $_type = 'QDM::RelatedPerson';


}

