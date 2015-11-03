<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Test\Dummy;
use Test\SetPayloadDummy;

class PayloadTest extends PHPUnit_Framework_TestCase
{
    public function testIssetElement()
    {
        $x = new Dummy;

        $this->assertTrue(isset($x['foo']));
        $this->assertFalse(isset($x['quux']));
    }

    public function testGetExistingElementByAlias()
    {
        $x = new Dummy;
        $elem = $x['foo'];

        $this->assertInstanceof('Dormilich\WebService\ARIN\Elements\Element', $elem);
    }

    public function testGetExistingElementByName()
    {
        $x = new Dummy;
        $elem = $x['bar'];

        $this->assertInstanceof('Dormilich\WebService\ARIN\Elements\Element', $elem);
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ARINException
     * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
     */
    public function testGetNonExistingElementFails()
    {
        $x = new Dummy;
        $x['quux'];
    }

    public function testSetExistingElement()
    {
        $x = new Dummy;
        $x['foo'] = 123;

        $this->assertSame('123', $x['foo']->getValue());
    }

    public function testSetExistingPayload()
    {
        $p = new SetPayloadDummy;
        $p['dummy']['foo'] = 1;

        $this->assertSame('1', $p['dummy']['foo']->getValue());

        $d = new Dummy;
        $d['foo'] = 2;
        $p['dummy'] = $d;

        $this->assertSame('2', $p['dummy']['foo']->getValue());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testReplaceExistingPayloadWithElementFails()
    {
        $p = new SetPayloadDummy;
        $p['dummy'] = new Element('abc');
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ARINException
     * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
     */
    public function testSetNonExistingElementFails()
    {
        $x = new Dummy;
        $x['quux'] = 123;
    }

    public function testUnsetExistingElement()
    {
        $x = new Dummy;

        // default state
        $this->assertFalse($x['foo']->isDefined());
        $this->assertSame('', $x['foo']->getValue());

        $x['foo'] = 123;

        // set state
        $this->assertTrue($x['foo']->isDefined());
        $this->assertSame('123', $x['foo']->getValue());

        unset($x['foo']);

        // unset state = default state
        $this->assertFalse($x['foo']->isDefined());
        $this->assertSame('', $x['foo']->getValue());
    }

    public function testUnsetNonexistingElementsIsIgnored()
    {
        $x = new Dummy;
        $x['foo'] = 123;

        unset($x['quux']);

        $this->assertSame('123', $x['foo']->getValue());
    }

    public function testForeachGetsNameAndElement()
    {
        $x = new Dummy;
        $x['foo'] = 1;

        $names = [];
        $values = [];

        foreach ($x as $name => $element) {
            $names[] = $name;
            $values[] = $element;
        }

        $this->assertEquals(['bar', 'list'], $names);
        $this->assertEquals([$x['foo'], $x['list']], $values);
    }

    public function testResetPayload()
    {
        $x = new Dummy;

        $x['foo'] = 1;

        $q = new Element('quux');
        $q->setValue(17);

        $x['list'] = $q;

        $this->assertTrue($x['foo']->isDefined());
        $this->assertTrue($x['list']->isDefined());

        $y = clone $x;

        $this->assertFalse($y['foo']->isDefined());
        $this->assertFalse($y['list']->isDefined());
    }
}
