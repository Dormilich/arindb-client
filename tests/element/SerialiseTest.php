<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\ArrayElement;
use Dormilich\WebService\ARIN\Elements\BoolElement;
use Dormilich\WebService\ARIN\Elements\FixedElement;
use Dormilich\WebService\ARIN\Lists\Group;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Test\Stringer;

class SerialiseTest extends PHPUnit_Framework_TestCase
{
    public function testSerialiseElementWithValue()
    {
        $doc = new DOMDocument;
        $elem = new Element('foo');
        $elem->setValue(1234);

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame('<foo>1234</foo>', $xml);
    }

    public function testSerialiseElementWithNamespace()
    {
        $doc = new DOMDocument;
        $elem = new Element('ns:foo', 'http://example.org/foo');
        $elem->setValue(1234);

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame('<ns:foo xmlns:ns="http://example.org/foo">1234</ns:foo>', $xml);
    }

    public function testSerialiseElementWithAttributes()
    {
        $doc = new DOMDocument;
        $elem = new Element('foo');
        $elem->abc = 'xyz';
        $elem->pi = '3.14';

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame('<foo abc="xyz" pi="3.14"/>', $xml);
    }

    public function testSerialiseElementWithEverything()
    {
        $doc = new DOMDocument;
        $elem = new Element('math:pi', 'http://example.org/mathematics');
        $elem->value = '3.14157';
        $elem->setValue('Pi is used for calculations with circles and spheres');

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $string  = '<math:pi xmlns:math="http://example.org/mathematics" value="3.14157">';
        $string .= 'Pi is used for calculations with circles and spheres</math:pi>';

        $this->assertSame($string, $xml);
    }

    public function testSerialiseBoolElement()
    {
        $doc = new DOMDocument;
        $elem = new BoolElement('foo');

        $elem->setValue(1);
        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertTrue($elem->getValue());
        $this->assertSame('<foo>true</foo>', $xml);

        $elem->setValue('false');
        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertFalse($elem->getValue());
        $this->assertSame('<foo>false</foo>', $xml);
    }

    public function testSerialiseFixedElement()
    {
        $doc = new DOMDocument;
        $elem = new FixedElement('foo', [2,4,6]);
        $elem->setValue(4);

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame('<foo>4</foo>', $xml);
    }

    public function testSerialiseLengthElement()
    {
        $doc = new DOMDocument;
        $elem = new LengthElement('foo', 3);
        $elem->setValue('ORG');

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame('<foo>ORG</foo>', $xml);
    }

    public function testSerialiseMultiLine()
    {
        $doc = new DOMDocument;
        $elem = new MultiLine('comment');
        $elem
            ->addValue('foo')
            ->addValue('bar')
            ->addValue('baz')
            ->addValue('quux')
        ;

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $string  = '<comment>';
        $string .= '<line number="1">foo</line>';
        $string .= '<line number="2">bar</line>';
        $string .= '<line number="3">baz</line>';
        $string .= '<line number="4">quux</line>';
        $string .= '</comment>';

        $this->assertSame($string, $xml);
    }

    public function testSerialiseGroup()
    {
        $doc = new DOMDocument;
        $item = new Element('item');
        $test = new Group('test');
        $group = new Group('list');

        $item->setValue(1);
        $group->addValue($item);

        $item2 = clone $item;
        $item2->setValue('abc');
        $group->addValue($item2);

        $item3 = clone $item;
        $item3->name = 'bar';
        $test->addValue($item3);
        $group->addValue($test);

        $node = $group->toDOM($doc);
        $xml = $doc->saveXML($node);

        // indentation is for better readability
        $string  = '<list>';
        $string .=   '<item>1</item>';
        $string .=   '<item>abc</item>';
        $string .=   '<test>';
        $string .=     '<item name="bar"/>';
        $string .=   '</test>';
        $string .= '</list>';

        $this->assertSame($string, $xml);
    }
}
