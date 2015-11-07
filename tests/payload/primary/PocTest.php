<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Poc;
use Dormilich\WebService\ARIN\Payloads\Phone;
use Test\Payload_TestCase;

class PocTest extends Payload_TestCase
{
	public function testSerialise()
	{
		$payload = new Poc;
		$phone = new Phone;

		$payload['country']['name']  = 'UNITED STATES';
		$payload['country']['code2'] = 'US';
		$payload['country']['code3'] = 'USA';
		$payload['country']['e164']  = '1';
		$payload['address']    = 'Line 1';
		$payload['city']       = 'Chantilly';
		$payload['state']      = 'VA';
		$payload['postalCode'] = '20151';
		$payload['emails']     = Element::createWith('email', 'you@example.com');
		$payload['comment']    = 'Line 1';
		$payload['created']    = 'Mon Nov 07 14:04:28 EST 2011';
		$payload['handle']     = 'ARIN-HOSTMASTER';
		$payload['type']       = 'PERSON';
		$payload['company']    = 'COMPANYNAME';
		$payload['firstName']  = 'FIRSTNAME';
		$payload['middleName'] = 'MIDDLENAME';
		$payload['lastName']   = 'LASTNAME';

		$phone['number'] = '+1.703.227.9840';
		$phone['extension'] = '101';
		$phone['type']['description'] = 'DESCRIPTION';
		$phone['type']['code'] = 'O';
		$payload['phones'] = $phone;

		$this->assertTrue($payload->isValid());

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('poc');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

	public function testParseXML()
	{
		$payload = new Poc;
		$payload->parse($this->loadXML('poc'));

		$this->assertSame([
			'iso3166-2' => 'VA',
			'iso3166-1' => [
				'name'  => 'UNITED STATES',
				'code2' => 'US',
				'code3' => 'USA',
				'e164'  => 1,
			],
			'emails' => [
				'you@example.com'
			],
			'streetAddress' => [
				'Line 1'
			],
			'city' => 'Chantilly',
			'postalCode'  => '20151',
			'comment' => [
				'Line 1'
			],
			'registrationDate' => 'Mon Nov 07 14:04:28 EST 2011',
			'handle'      => 'ARIN-HOSTMASTER',
			'contactType' => 'PERSON',
			'companyName' => 'COMPANYNAME',
			'firstName'   => 'FIRSTNAME',
			'middleName'  => 'MIDDLENAME',
			'lastName'    => 'LASTNAME',
			'phones' => [[
				'type' => [
					'description' => 'DESCRIPTION',
					'code' => 'O',
				],
				'number' => '+1.703.227.9840',
				'extension' => '101',
			]],
		], $payload->getValue());
	}
}
