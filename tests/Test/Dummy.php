<?php

namespace Test;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\Group;
use Dormilich\WebService\ARIN\Lists\MultiLine;
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
        $this->create(new Group('list'));
        $this->create(new MultiLine('comment'));
    }

    public function setValue($value)
    {
        throw new \UnexpectedValueException('This is not an Element.');
    }
}
