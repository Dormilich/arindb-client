<?php

use Dormilich\WebService\ARIN\Payloads\AttachmentReference;
use Dormilich\WebService\ARIN\Payloads\MessageReference;
use Test\Payload_TestCase;

class MessageReferenceTest extends Payload_TestCase
{
	public function testIdProperty()
	{
		$payload = new MessageReference;

		$this->assertFalse($payload['id']->isValid());
		$this->assertNull($payload['id']->getValue());

		$payload['id'] = 'qzufqn';

		$this->assertTrue($payload['id']->isValid());
		$this->assertSame('qzufqn', $payload['id']->getValue());

		unset($payload['id']);
		$this->assertFalse($payload['id']->isValid());

		// using name instead of alias

		$payload['messageId'] = 'zenzezgeen';

		$this->assertSame('zenzezgeen', $payload['messageId']->getValue());
		$this->assertSame('zenzezgeen', $payload['id']->getValue());
	}

	public function testReferencesProperty()
	{
		$payload = new MessageReference;

		$attachment = new AttachmentReference;
		$attachment['id'] = 'ABC123';
		$attachment['filename'] = 'test.exe';

		$this->assertFalse($payload['attachmentReferences']->isValid());
		$this->assertCount(0, $payload['attachmentReferences']);

		$payload['attachmentReferences'] = clone $attachment;

		$this->assertFalse($payload['attachmentReferences']->isValid());
		$this->assertCount(1, $payload['attachmentReferences']);

		$payload['attachmentReferences']->addValue($attachment);

		$this->assertTrue($payload['attachmentReferences']->isValid());
		$this->assertCount(2, $payload['attachmentReferences']);

		unset($payload['attachmentReferences']);
		$this->assertCount(0, $payload['attachmentReferences']);
	}

	public function testGetPayloadAsArray()
	{
		$payload = new MessageReference;

		$eins = new AttachmentReference;
		$eins['id'] = 'ABC123';
		$eins['filename'] = 'test.exe';

		$zwei = new AttachmentReference;
		$zwei['id'] = 'BAR';
		$zwei['filename'] = 'foo.exe';

		$payload['id'] = '7ZERNX1O3487ZHNE';

		$this->assertSame([
			'attachmentReferences' => [],
			'messageId' => '7ZERNX1O3487ZHNE',
		], $payload->getValue());

		$payload['attachmentReferences'][] = $eins;
		$payload['attachmentReferences'][] = $zwei;

		$this->assertSame([
			'attachmentReferences' => [[
				'attachmentFilename' => 'test.exe',
				'attachmentId' => 'ABC123',
			], [
				'attachmentFilename' => 'foo.exe',
				'attachmentId' => 'BAR',
			]],
			'messageId' => '7ZERNX1O3487ZHNE',
		], $payload->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
	 */
	public function testSetUnknownPropertyFails()
	{
		$payload = new MessageReference;
		$payload['message'] = 'The quick brown fox...';
	}

	public function testValidity()
	{
		$payload = new MessageReference;

		$attachment = new AttachmentReference;
		$attachment['id'] = 'ABC123';
		$attachment['filename'] = 'test.exe';

		$this->assertFalse($payload->isValid());

		$payload['attachmentReferences'] = $attachment;
		$this->assertFalse($payload->isValid());

		$payload['id'] = 'ZKUEWXF';
		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new MessageReference;

		$attachment = new AttachmentReference;
		$attachment['id'] = 'ATTACHMENTID';
		$attachment['filename'] = 'ATTACHMENTFILENAME';

		$payload['id'] = 'MESSAGEID';
		$payload['attachmentReferences'] = $attachment;

		$this->assertTrue($payload->isValid());

		$doc = new DOMDocument;
		$node = $payload->toDOM($doc);
		$actual = $doc->saveXML($node);

		$xml = $this->loadDOM('message-reference');
		$expected = $xml->saveXML($xml->documentElement);

		$this->assertSame($expected, $actual);
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage This Message Reference Payload should not be submitted by itself.
	 */
	public function testSerialiseAsRequestPayloadFails()
	{
		$payload = new MessageReference;
		$payload['id'] = 'MESSAGEID';

		$xml = $payload->toXML();
	}

	public function testParseXML()
	{
		$payload = new MessageReference;
		$payload->parse($this->loadXML('message-reference'));

		$this->assertSame([
			'attachmentReferences' => [[
				'attachmentFilename' => 'ATTACHMENTFILENAME',
				'attachmentId' => 'ATTACHMENTID',
			]],
			'messageId' => 'MESSAGEID',
		], $payload->getValue());
	}
}
