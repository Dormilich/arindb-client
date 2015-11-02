<?php

namespace Test;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\GroupElement;
use Dormilich\WebService\ARIN\Payloads\Payload;

class Dummy extends Payload
{
    public function __construct()
    {
        $this->name = 'dummy';
        $this->init();
    }

    protected function init()
    {
        $this->create(new Element('bar'), 'foo');
        $this->create(new GroupElement('list'));
    }
}
