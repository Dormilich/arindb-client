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

        $this->assertEquals(['bar', 'list', 'comment'], $names);
        $this->assertEquals([$x['foo'], $x['list'], $x['comment']], $values);
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

    public function testSerialiseEmptyPayload()
    {
        $d = new Dummy;

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . \PHP_EOL;
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1"/>' . \PHP_EOL;

        $this->assertSame($xml, $d->toXML()->saveXML());
    }

    public function testSerialisePayloadWithSpecifiedEncoding()
    {
        $d = new Dummy;

        $xml  = '<?xml version="1.0" encoding="ISO-8859-1"?>' . \PHP_EOL;
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1"/>' . \PHP_EOL;

        $this->assertSame($xml, $d->toXML("ISO-8859-1")->saveXML());
    }

    public function testSerialisePayloadWithSimpleElement()
    {
        $d = new Dummy;
        $d['foo'] = 'quux';

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . \PHP_EOL;
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1">';
        $xml .=   '<bar>quux</bar>';
        $xml .= '</dummy>' . \PHP_EOL;

        $this->assertSame($xml, $d->toXML()->saveXML());
    }

    public function testSerialisePayloadWithListElement()
    {
        $d = new Dummy;
        $d['comment']
            ->addValue('I hope')
            ->addValue('this doesn’t')
            ->addValue('blow up!')
        ;

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . \PHP_EOL;
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1">';
        $xml .=   '<comment>';
        $xml .=     '<line number="1">I hope</line>';
        $xml .=     '<line number="2">this doesn’t</line>'; // this only works because of the encoding
        $xml .=     '<line number="3">blow up!</line>';
        $xml .=   '</comment>';
        $xml .= '</dummy>' . \PHP_EOL;

        $this->assertSame($xml, $d->toXML()->saveXML());
    }

    public function testSerialiseFullPayload()
    {
        $d = new Dummy;

        $d['foo'] = 'quux';

        $d['comment']
            ->addValue('This is')
            ->addValue('booooring')
        ;

        $e  = new Element('error');
        $e->setValue('too late to be true.');
        $d['list'] = $e;

        $f  = new Element('warning');
        $f->setValue('watch out!');
        $d['list']->addValue($f);

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . \PHP_EOL;
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1">';
        $xml .=   '<bar>quux</bar>';
        $xml .=   '<list>';
        $xml .=     '<error>too late to be true.</error>';
        $xml .=     '<warning>watch out!</warning>';
        $xml .=   '</list>';
        $xml .=   '<comment>';
        $xml .=     '<line number="1">This is</line>';
        $xml .=     '<line number="2">booooring</line>';
        $xml .=   '</comment>';
        $xml .= '</dummy>' . \PHP_EOL;

        $this->assertSame($xml, $d->toXML()->saveXML());
    }
}
