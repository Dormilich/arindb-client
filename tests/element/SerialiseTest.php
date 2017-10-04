<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Boolean;
use Dormilich\WebService\ARIN\Elements\Integer;
use Dormilich\WebService\ARIN\Elements\IP;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Elements\Selection;

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

    public function testSerialiseBoolean()
    {
        $doc = new DOMDocument;
        $elem = new Boolean('foo');

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

    public function testSerialiseSelection()
    {
        $doc = new DOMDocument;
        $elem = new Selection('foo', [2,4,6]);
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

    public function testSerialiseInteger()
    {
        $doc = new DOMDocument;
        $elem = new Integer('foo');
        $elem->setValue(4);

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame('<foo>4</foo>', $xml);
    }

    public function testSerialiseIP()
    {
        $doc = new DOMDocument;
        $elem = new IP('foo');
        $elem->setValue('192.168.17.5');

        $node = $elem->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame('<foo>192.168.17.5</foo>', $xml);
    }
}
