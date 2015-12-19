<?php

use Dormilich\WebService\ARIN\Payloads\AttachmentReference;
use Test\Payload_TestCase;

class AttachmentReferenceTest extends Payload_TestCase
{
	public function testIdProperty()
	{
		$payload = new AttachmentReference;

		$this->assertFalse($payload['id']->isValid());
		$this->assertNull($payload['id']->getValue());

		$payload['id'] = 'paperwork';

		$this->assertTrue($payload['id']->isValid());
		$this->assertSame('paperwork', $payload['id']->getValue());
	}

	public function testIdPropertyWithOriginalName()
	{
		$payload = new AttachmentReference;

		$payload['attachmentId'] = 'legacy';

		$this->assertSame('legacy', $payload['id']->getValue());
		$this->assertSame('legacy', $payload['attachmentId']->getValue());
	}

	public function testFilenameProperty()
	{
		$payload = new AttachmentReference;

		$this->assertFalse($payload['filename']->isValid());
		$this->assertNull($payload['filename']->getValue());

		$payload['filename'] = 'test.exe';

		$this->assertTrue($payload['filename']->isValid());
		$this->assertSame('test.exe', (string) $payload['filename']);

		unset($payload['filename']);
		$this->assertFalse($payload['filename']->isValid());

		$payload['attachmentFilename'] = 'legacy';

		$this->assertSame('legacy', $payload['filename']->getValue());
		$this->assertSame('legacy', $payload['attachmentFilename']->getValue());
	}

	public function testGetPayloadAsArray()
	{
		$payload = new AttachmentReference;

		$payload['id'] = 'paperwork';
		$this->assertSame([
			'attachmentFilename' => NULL, 
			'attachmentId' => 'paperwork', 
		], $payload->getValue());

		$payload['filename'] = 'test.exe';
		$this->assertSame([
			'attachmentFilename' => 'test.exe', 
			'attachmentId' => 'paperwork', 
		], $payload->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
	 */
	public function testSetUnknownpropertyFails()
	{
		$payload = new AttachmentReference;
		$payload['undefined'] = 'paperwork';
	}

	public function testValidity()
	{
		$payload = new AttachmentReference;

		$this->assertFalse($payload->isValid());
 
		$payload['id'] = 'paperwork';
		$this->assertFalse($payload->isValid());

		$payload['filename'] = 'test.exe';
		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new AttachmentReference;

		$payload['id'] = 'ATTACHMENTID';
		$payload['filename'] = 'ATTACHMENTFILENAME';

		$this->assertTrue($payload->isValid());

		$doc = new DOMDocument;
		$node = $payload->toDOM($doc);
		$actual = $doc->saveXML($node);

		$xml = $this->loadDOM('attachment-reference');
		$expected = $xml->saveXML($xml->documentElement);

		$this->assertSame($expected, $actual);
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage This Attachment Reference Payload should not be submitted by itself.
	 */
	public function testSerialiseAsRequestPayloadFails()
	{
		$payload = new AttachmentReference;
		$payload['id'] = 'ATTACHMENTID';

		$xml = $payload->toXML();

	}

	public function testParseXML()
	{
		$payload = new AttachmentReference;
		$payload->parse($this->loadXML('attachment-reference'));

		$this->assertSame([
			'attachmentFilename' => 'ATTACHMENTFILENAME', 
			'attachmentId' => 'ATTACHMENTID', 
		], $payload->getValue());
	}
}
