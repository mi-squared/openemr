<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\Practitioner
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class Practitioner extends Entity
{

    /**
     * @property System.Code $role
     */
    public $role = null;

    /**
     * @property System.Code $specialty
     */
    public $specialty = null;

    /**
     * @property System.Code $qualification
     */
    public $qualification = null;

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.137';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '2.16.840.1.113883.10.20.24.3.162';

    public $_type = 'QDM::Practitioner';


}

