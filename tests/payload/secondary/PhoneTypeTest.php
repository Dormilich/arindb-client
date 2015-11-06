<?php

use Dormilich\WebService\ARIN\Payloads\PhoneType;
use Test\Payload_TestCase;

class PhoneTypeTest extends Payload_TestCase
{
	public function testDescriptionProperty()
	{
		$payload = new PhoneType;

		$this->assertFalse($payload['description']->isValid());
		$this->assertNull($payload['description']->getValue());

		$payload['description'] = 'The quick brown fox jumped over the lazy dog';

		$this->assertTrue($payload['description']->isValid());
		$this->assertSame('The quick brown fox jumped over the lazy dog', $payload['description']->getValue());

		unset($payload['description']);
		$this->assertFalse($payload['description']->isValid());
	}

	public function testCodeProperty()
	{
		$payload = new PhoneType;

		$this->assertFalse($payload['code']->isValid());
		$this->assertNull($payload['code']->getValue());

		$payload['code'] = 'M';

		$this->assertTrue($payload['code']->isValid());
		$this->assertSame('M', $payload['code']->getValue());

		unset($payload['code']);
		$this->assertFalse($payload['code']->isValid());
	}

	public function validCodeValueProvider()
	{
		return [
			['O'], ['F'], ['M'], 
		];
	}

	/**
	 * @dataProvider validCodeValueProvider
	 */
	public function testCodePropertyWithValidInput($value)
	{
		$payload = new PhoneType;
		$payload['code'] = $value;

		$this->assertSame($value, (string) $payload['code']);
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testCodePropertyWithInvalidInput()
	{
		$payload = new PhoneType;
		$payload['code'] = 8;
	}

	public function testGetPayloadAsArray()
	{
		$payload = new PhoneType;

		$payload['code'] = 'F';

		$this->assertSame(['code' => 'F'], $payload->getValue(true));
		$this->assertSame([
			'description' => NULL, 
			'code' => 'F', 
		], $payload->getValue());

		$payload['description'] = 'some comment';

		$this->assertSame([
			'description' => 'some comment', 
			'code' => 'F', 
		], $payload->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
	 */
	public function testSetUnknownPropertyFails()
	{
		$payload = new PhoneType;
		$payload['number'] = '007';
	}

	public function testValidity()
	{
		$payload = new PhoneType;

		$this->assertFalse($payload->isValid());

		$payload['description'] = 'some comment';
		$this->assertFalse($payload->isValid());

		$payload['code'] = 'M';
		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new PhoneType;

		$payload['code'] = 'O';
		$payload['description'] = 'DESCRIPTION';

		$this->assertTrue($payload->isValid());

		$doc = new DOMDocument;
		$node = $payload->toDOM($doc);
		$actual = $doc->saveXML($node);

		$xml = $this->loadDOM('phone-type');
		$expected = $xml->saveXML($xml->documentElement);

		$this->assertSame($expected, $actual);
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage This Phone Type Payload should not be submitted by itself.
	 */
	public function testSerialiseAsRequestPayloadFails()
	{
		$payload = new PhoneType;
		$payload['code'] = 'F';

		$xml = $payload->toXML();
	}

	public function testParseXML()
	{
		$payload = new PhoneType;
		$payload->parse($this->loadXML('phone-type'));

		$this->assertSame([
			'description' => 'DESCRIPTION', 
			'code' => 'O', 
		], $payload->getValue());
	}
}
