<?php


namespace OpenEMR\Cqm\Qdm\BaseTypes;


class DataElement extends AbstractType
{
    public $bundleId;
    public $_type;
    public $dataElementCodes = [];

    public function __construct(array $properties = [])
    {
        $this->_type = get_class($this);
        parent::__construct($properties);
    }

    public function addCode(Code $code)
    {
        $this->dataElementCodes[] = $code;
    }
}
