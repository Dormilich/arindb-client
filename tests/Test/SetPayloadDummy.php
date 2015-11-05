<?php

namespace Test;

use Dormilich\WebService\ARIN\Payloads\Payload;

/**
 * This payload is used to test the behaviour of a payload that contains a sub-payload.
 */
class SetPayloadDummy extends Payload
{
    public function __construct()
    {
        $this->name = 'wrapper';
        $this->init();
    }

    protected function init()
    {
        $dummy = new Dummy;
        $this->elements['dummy'] = $dummy;
    }

    public function isValid()
    {
        return $this->elements['dummy']->isValid();
    }
}
