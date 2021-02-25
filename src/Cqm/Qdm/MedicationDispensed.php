<?php

namespace OpenEMR\Cqm\Qdm;

/**
 * OpenEMR\Cqm\Qdm\MedicationDispensed
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @QDM Version 5.5
 * @author Ken Chapple <ken@mi-squared.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General
 * Public License 3
 */
class MedicationDispensed extends QDMBaseType
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
     * @property System.Integer $refills
     */
    public $refills = null;

    /**
     * @property System.Quantity $dosage
     */
    public $dosage = null;

    /**
     * @property System.Quantity $supply
     */
    public $supply = null;

    /**
     * @property System.Code $frequency
     */
    public $frequency = null;

    /**
     * @property System.Integer $daysSupplied
     */
    public $daysSupplied = null;

    /**
     * @property System.Code $route
     */
    public $route = null;

    /**
     * @property System.Any $prescriber
     */
    public $prescriber = null;

    /**
     * @property System.Any $dispenser
     */
    public $dispenser = null;

    /**
     * @property System.Code $negationRationale
     */
    public $negationRationale = null;

    /**
     * @property System.String $hqmfOid
     */
    public $hqmfOid = '2.16.840.1.113883.10.20.28.4.49';

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
    public $qdmStatus = 'dispensed';

    public $_type = 'QDM::MedicationDispensed';


}

