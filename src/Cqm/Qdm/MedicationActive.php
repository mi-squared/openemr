<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\MedicationActive
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class MedicationActive extends QDMBaseType
{

    /**
     * @property System.DateTime $relevantDatetime
     */
    public $relevantDatetime = null;

    /**
     * @property interval<System.DateTime> $relevantPeriod
     */
    public $relevantPeriod = null;

    /**
     * @property System.Quantity $dosage
     */
    public $dosage = null;

    /**
     * @property System.Code $frequency
     */
    public $frequency = null;

    /**
     * @property System.Code $route
     */
    public $route = null;

    /**
     * @property System.Any $recorder
     */
    public $recorder = null;

    /**
     * @property System.String $qdmTitle
     */
    public $qdmTitle = 'Medication, Active';

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.44';

    /**
     * @property System.String $qrdaOid
     */
    public $qrdaOid = '';

    /**
     * @property System.String $qdmCategory
     */
    public $qdmCategory = 'medication';

    /**
     * @property System.String $qdmStatus
     */
    public $qdmStatus = 'active';

    public $_type = 'QDM::MedicationActive';


}

