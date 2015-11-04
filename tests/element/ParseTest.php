<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Boolean;
use Dormilich\WebService\ARIN\Elements\Integer;
use Dormilich\WebService\ARIN\Elements\IP;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Elements\Selection;

class ParseTest extends PHPUnit_Framework_TestCase
{
    public function createElement(\SimpleXMLElement $sxe)
    {
        $ns = $sxe->getNamespaces();

        if ($uri = current($ns)) {
            return new Element(key($ns).':'.$sxe->getName(), $uri);
        }
        return new Element($sxe->getName());
    }

    public function testParseElementWithValue()
    {
        $xml = simplexml_load_string('<test>1234</test>');
        $f = $this->createElement($xml);
        $f->parse($xml);

        $this->assertSame('1234', $f->getValue());
    }

    public function testParseElementWithNamespace()
    {
        $xml = simplexml_load_string('<ns:foo xmlns:ns="http://example.org/foo">1234</ns:foo>');
        $e = $this->createElement($xml);
        $e->parse($xml);

        $this->assertSame('1234', $e->getValue());
    }

    public function testParseElementWithAttributes()
    {
        $xml = simplexml_load_string('<foo abc="xyz" pi="3.14"/>');
        $e = $this->createElement($xml);
        $e->parse($xml);

        $this->assertSame('', $e->getValue());
        $this->assertSame('xyz', $e->abc);
        $this->assertSame('3.14', $e->pi);
    }

    public function testParseElementWithEverything()
    {
        $string  = '<math:pi xmlns:math="http://example.org/mathematics" value="3.14157">';
        $string .= 'Pi is used for calculations with circles and spheres</math:pi>';
        $xml = simplexml_load_string($string);
        $e = $this->createElement($xml);
        $e->parse($xml);
        
        $this->assertSame('Pi is used for calculations with circles and spheres', $e->getValue());
        $this->assertSame('3.14157', $e->value);
    }

    public function testParseAndReserialiseXMLwithNamespace()
    {
        $string  = '<math:pi xmlns:math="http://example.org/mathematics" value="3.14157">';
        $string .= 'Pi is used for calculations with circles and spheres</math:pi>';

        $sxe = simplexml_load_string($string);
        $e = $this->createElement($sxe);
        $e->parse($sxe);

        $doc = new DOMDocument;
        $node = $e->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame($string, $xml);
    }

    public function testParseAndReserialiseXMLwithoutNamespace()
    {
        $string = '<pi value="3.14157">Pi is used for calculations with circles and spheres</pi>';

        $sxe = simplexml_load_string($string);
        $e = $this->createElement($sxe);
        $e->parse($sxe);

        $doc = new DOMDocument;
        $node = $e->toDOM($doc);
        $xml = $doc->saveXML($node);

        $this->assertSame($string, $xml);
    }
}
