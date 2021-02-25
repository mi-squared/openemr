<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\QDMBaseType
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class QDMBaseType extends \OpenEMR\Cqm\Qdm\BaseTypes\DataElement
{

    /**
     * @property System.String $id
     */
    public $id = null;

    /**
     * @property System.Code $code
     */
    public $code = null;

    /**
     * @property System.String $patientId
     */
    public $patientId = null;

    /**
     * @property System.String $qdmVersion
     */
    public $qdmVersion = '5.5';

    public $_type = 'QDM::QDMBaseType';


}

