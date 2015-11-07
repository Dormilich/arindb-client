<?php

use Dormilich\WebService\ARIN\Payloads\Phone;
use Test\Payload_TestCase;

class PhoneTest extends Payload_TestCase
{
	public function testValidity()
	{
		$payload = new Phone;

		$payload['number'] = '+1.703.227.9840';
		$payload['type']['code'] = 'O';
		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new Phone;

		$payload['number'] = '+1.703.227.9840';
		$payload['extension'] = '101';
		$payload['type']['code'] = 'O';
		$payload['type']['description'] = 'DESCRIPTION';

		$this->assertTrue($payload->isValid());

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('phone');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

	public function testParseXML()
	{
		$payload = new Phone;
		$payload->parse($this->loadXML('phone'));

		$this->assertSame([
			'type' => [
				'description' => 'DESCRIPTION',
				'code' => 'O',
			],
			'number' => '+1.703.227.9840',
			'extension' => '101',
		], $payload->getValue());
	}
}
