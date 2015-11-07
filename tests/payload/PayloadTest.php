<?php

use Dormilich\WebService\ARIN\XMLHandler as XML;
use Dormilich\WebService\ARIN\Elements\Element;
use Test\Dummy;
use Test\SetPayloadDummy;

/* Setup for Dummy:
Dummy
    bar [foo] (required)
    list
        error
        warning
        notice
    comment (multi-line)
*/

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

    public function testAppendTextToExistingElement()
    {
        $x = new Dummy;

        $x['foo'] = 123;
        $this->assertSame('123', $x['foo']->getValue());

        $x['foo'] .= 456;
        $this->assertSame('123456', $x['foo']->getValue());
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
     * @expectedException PHPUnit_Framework_Error
     */
    public function testReplaceExistingPayloadWithElementFailsSilently()
    {
        $p = new SetPayloadDummy;
        $p['dummy'] = new Element('abc');
    }

    /**
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
        $this->assertFalse($x['foo']->isValid());
        $this->assertNull($x['foo']->getValue());

        $x['foo'] = 123;

        // set state
        $this->assertTrue($x['foo']->isValid());
        $this->assertSame('123', $x['foo']->getValue());

        unset($x['foo']);

        // unset state = default state
        $this->assertFalse($x['foo']->isValid());
        $this->assertNull($x['foo']->getValue());
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
        $x['list'] = Element::createWith('notice', 'look behind');

        $this->assertTrue($x['foo']->isValid());
        $this->assertTrue($x['list']->isValid());

        $y = clone $x;

        $this->assertFalse($y['foo']->isValid());
        $this->assertFalse($y['list']->isValid());
    }

    public function testPayloadValidity()
    {
        $dummy = new Dummy;

        $container = new SetPayloadDummy;
        $container['dummy'] = $dummy;

        $dummy['comment'] = 'a comment line';
        $this->assertFalse($dummy->isValid());
        $this->assertFalse($container->isValid());

        $dummy['foo'] = 'bar';
        $this->assertTrue($dummy->isValid());
        $this->assertTrue($container->isValid());
    }

    public function testFilterSupportsElementList()
    {
        $d = new Dummy;
        $filtered = $d->filter('comment', 'list');
        $this->assertCount(2, $filtered);

        // definition order, not filter call order
        $this->assertSame(['list', 'comment'], array_keys($filtered));
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ParserException
     */
    public function testParseEmptyPayload()
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1"/>';
        $xml = simplexml_load_string($xml);

        $d = new Dummy;
        $d->parse($xml);

        $this->assertFalse($d->isValid());
        $d->toXML('UTF-8', XML::VALIDATE)->saveXML();
    }

    public function testSerialiseEmptyPayload()
    {
        $d = new Dummy;

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . \PHP_EOL;
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1"/>' . \PHP_EOL;

        $this->assertSame($xml, $d->toXML('UTF-8', XML::NOVALIDATE)->saveXML());
    }

    public function testSerialisePayloadWithSpecifiedEncoding()
    {
        $d = new Dummy;

        $xml  = '<?xml version="1.0" encoding="ISO-8859-1"?>' . \PHP_EOL;
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1"/>' . \PHP_EOL;

        $this->assertSame($xml, $d->toXML("ISO-8859-1", XML::NOVALIDATE)->saveXML());
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

    public function testParsePayloadWithSimpleElement()
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<dummy xmlns="http://www.arin.net/regrws/core/v1"><bar>quux</bar></dummy>';
        $xml = simplexml_load_string($xml);

        $d = new Dummy;
        $d->parse($xml);

        $this->assertTrue($d->isValid());
        $this->assertSame('quux', (string) $d['bar']);

        $this->assertFalse($d['list']->isValid());
        $this->assertFalse($d['comment']->isValid());
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

        $this->assertSame($xml, $d->toXML('UTF-8', XML::NOVALIDATE)->saveXML());
    }

    public function testParsePayloadWithListElement()
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?><dummy xmlns="http://www.arin.net/regrws/core/v1">';
        $xml .= '<comment><line number="1">I hope</line><line number="2">this doesn’t</line>';
        $xml .= '<line number="3">blow up!</line></comment></dummy>';
        $xml = simplexml_load_string($xml);

        $d = new Dummy;
        $d->parse($xml);

        $this->assertCount(3, $d['comment']);
        $this->assertSame('I hope', $d['comment'][0]);
        $this->assertSame('this doesn’t', $d['comment'][1]);
        $this->assertSame('blow up!', $d['comment'][2]);
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

    public function testParseFullPayload()
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?><dummy xmlns="http://www.arin.net/regrws/core/v1">';
        $xml .= '<bar>quux</bar><list><notice>too late to be true.</notice><warning>watch out!</warning>';
        $xml .= '</list><comment><line number="1">This is</line><line number="2">booooring</line>';
        $xml .= '</comment></dummy>';
        $xml = simplexml_load_string($xml);

        $d = new Dummy;
        $d->parse($xml);

        $this->assertTrue($d->isValid());
        $this->assertSame('quux', (string) $d['bar']);

        $this->assertCount(2, $d['list']);
        $this->assertSame('too late to be true.', (string) $d['list']['notice']);
        $this->assertSame('watch out!', (string) $d['list']['warning']);

        $this->assertCount(2, $d['comment']);
        $this->assertSame('This is', $d['comment'][0]);
        $this->assertSame('booooring', $d['comment'][1]);
    }
}
