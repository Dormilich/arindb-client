<?php

namespace Test;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\NamedGroup;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Dormilich\WebService\ARIN\Payloads\Payload;

/**
 * This class is used to test the Payload object's functionality, since that 
 * object cannot be instantiated directly.
 */
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
        $this->create(new NamedGroup('list', ['error', 'warning', 'notice']));
        $this->create(new MultiLine('comment'));
    }

    public function isValid()
    {
        return $this->get('foo')->isValid();
    }
}
