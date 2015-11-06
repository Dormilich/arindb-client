<?php

use Dormilich\WebService\ARIN\Payloads\Attachment;
use Test\Payload_TestCase;

class AttachmentTest extends Payload_TestCase
{
    public function testDataProperty()
    {
        $payload = new Attachment;

        $this->assertFalse($payload['data']->isValid());
        $this->assertNull($payload['data']->getValue());

        $payload['data'] = 'paperwork';

        $this->assertTrue($payload['data']->isValid());
        $this->assertSame('paperwork', $payload['data']->getValue());

        unset($payload['data']);
        $this->assertFalse($payload['data']->isValid());
    }

    public function testFilenameProperty()
    {
        $payload = new Attachment;

        $this->assertFalse($payload['filename']->isValid());
        $this->assertNull($payload['filename']->getValue());

        $payload['filename'] = 'test.exe';

        $this->assertTrue($payload['filename']->isValid());
        $this->assertSame('test.exe', (string) $payload['filename']);

        unset($payload['filename']);
        $this->assertFalse($payload['filename']->isValid());
    }

    public function testGetPayloadAsArray()
    {
        $payload = new Attachment;

        $payload['data'] = 'paperwork';

        $this->assertSame(['data' => 'paperwork'], $payload->getValue(true));
        $this->assertSame(['data' => 'paperwork', 'filename' => NULL], $payload->getValue());

        $payload['filename'] = 'test.exe';

        $this->assertSame(['data' => 'paperwork', 'filename' => 'test.exe'], $payload->getValue());
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
     */
    public function testSetUnknownpropertyFails()
    {
        $payload = new Attachment;
        $payload['undefined'] = 'paperwork';
    }

    public function testValidity()
    {
        $payload = new Attachment;

        $this->assertFalse($payload->isValid());
        $this->assertFalse($payload->isValid());

        $payload['data'] = 'paperwork';
        $this->assertTrue($payload->isValid());

        unset($payload['data']);
        $this->assertFalse($payload->isValid());

        $payload['filename'] = 'test.exe';
        $this->assertTrue($payload->isValid());
    }

    public function testSerialise()
    {
        $payload = new Attachment;

        $payload['data'] = 'DATA';
        $payload['filename'] = 'FILENAME';

        $this->assertTrue($payload->isValid());

        $doc = new DOMDocument;
        $node = $payload->toDOM($doc);
        $actual = $doc->saveXML($node);

        $xml = $this->loadDOM('attachment');
        $expected = $xml->saveXML($xml->documentElement);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage This Attachment Payload should not be submitted by itself.
     */
    public function testSerialiseAsRequestPayloadFails()
    {
        $payload = new Attachment;
        $payload['data'] = 'DATA';

        $xml = $payload->toXML();

    }

    public function testParseXML()
    {
        $payload = new Attachment;
        $payload->parse($this->loadXML('attachment'));

        $this->assertSame(['data' => 'DATA', 'filename' => 'FILENAME'], $payload->getValue());
    }
}
