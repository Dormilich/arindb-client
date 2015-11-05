<?php

use Dormilich\WebService\ARIN\Payloads\Component;
use Test\Payload_TestCase;

class ComponentTest extends Payload_TestCase
{
    public function testNameProperty()
    {
        $payload = new Component;

        $this->assertFalse($payload['name']->isDefined());
        $this->assertNull($payload['name']->getValue());

        $payload['name'] = 'error';

        $this->assertTrue($payload['name']->isDefined());
        $this->assertSame('error', $payload['name']->getValue());

        unset($payload['name']);
        $this->assertFalse($payload['name']->isDefined());
    }

    public function testMessageProperty()
    {
        $payload = new Component;

        $this->assertFalse($payload['message']->isDefined());
        $this->assertNull($payload['message']->getValue());

        $payload['message'] = 'this is an error';

        $this->assertTrue($payload['message']->isDefined());
        $this->assertSame('this is an error', (string) $payload['message']);

        unset($payload['message']);
        $this->assertFalse($payload['message']->isDefined());
    }

    public function testGetPayloadAsArray()
    {
        $payload = new Component;

        $payload['name'] = 'foo';

        $this->assertSame(['name' => 'foo'], $payload->getValue(true));
        $this->assertSame(['name' => 'foo', 'message' => NULL], $payload->getValue());

        $payload['message'] = 'bar';

        $this->assertSame(['name' => 'foo', 'message' => 'bar'], $payload->getValue());
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
     */
    public function testSetUnknownPropertyFails()
    {
        $payload = new Component;
        $payload['severity'] = 'warning';
    }

    public function testValidity()
    {
        $payload = new Component;

        $this->assertFalse($payload->isDefined());
        $this->assertFalse($payload->isValid());

        $payload['name'] = 'error';
        $this->assertFalse($payload->isValid());

        $payload['message'] = 'doesn’t work';
        $this->assertTrue($payload->isValid());
    }

    public function testSerialise()
    {
        $payload = new Component;

        $payload['name'] = 'NAME';
        $payload['message'] = 'MESSAGE';

        $this->assertTrue($payload->isValid());

        $doc = new DOMDocument;
        $node = $payload->toDOM($doc);
        $actual = $doc->saveXML($node);

        $xml = $this->loadDOM('component');
        $expected = $xml->saveXML($xml->documentElement);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage This Component Error Payload should not be submitted by itself.
     */
    public function testSerialiseAsRequestPayloadFails()
    {
        $payload = new Component;
        $payload['name'] = 'no good';

        $xml = $payload->toXML();
    }

    public function testParseXML()
    {
        $payload = new Component;
        $payload->parse($this->loadXML('component'));

        $this->assertSame(['name' => 'NAME', 'message' => 'MESSAGE'], $payload->getValue());
    }
}
