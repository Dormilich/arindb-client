<?php

namespace Test;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\GroupElement;
use Dormilich\WebService\ARIN\Payloads\Payload;

class SetPayloadDummy extends Payload
{
    public function __construct()
    {
        $this->name = 'dummy';
        $this->init();
    }

    protected function init()
    {
        $dummy = new Dummy;
        $this->elements[$dummy->getName()] = $dummy;
    }
}
