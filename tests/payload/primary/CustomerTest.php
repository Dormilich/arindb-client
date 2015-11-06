<?php

use Dormilich\WebService\ARIN\Payloads\Customer;
use Test\Payload_TestCase;

class CustomerTest extends Payload_TestCase
{
	public function testValidity()
	{
		$payload = new Customer;

		$this->assertFalse($payload->isValid());

		$payload['handle'] = 'CUSTOMERHANDLE';
		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new Customer;

		$payload['name']    = 'CUSTOMERNAME';
		$payload['country']['name']  = 'UNITED STATES';
		$payload['country']['code2'] = 'US';
		$payload['country']['code3'] = 'USA';
		$payload['country']['e164']  = 1;
		$payload['handle']  = 'CUST';
		$payload['address'] = 'Line 1';
		$payload['city']    = 'Chantilly';
		$payload['state']   = 'VA';
		$payload['postalCode'] = '20151';
		$payload['comment'] = 'Line 1';
		$payload['org']     = 'PARENTORGHANDLE';
		$payload['created'] = 'Mon Nov 07 14:04:28 EST 2011';
		$payload['private'] = 'off';

		$this->assertTrue($payload->isValid());

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('customer');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

	public function testParseXML()
	{
		$payload = new Customer;
		$payload->parse($this->loadXML('customer'));

		$this->assertSame([
			'customerName' => 'CUSTOMERNAME', 
			'iso3166-1' => [
				'name'  => 'UNITED STATES', 
				'code2' => 'US', 
				'code3' => 'USA', 
				'e164'  => 1, 
			],
			'handle' => 'CUST', 
			'streetAddress' => ['Line 1'], 
			'city' => 'Chantilly', 
			'iso3166-2' => 'VA', 
			'postalCode' => '20151', 
			'comment' => ['Line 1'], 
			'parentOrgHandle' => 'PARENTORGHANDLE', 
			'registrationDate' => 'Mon Nov 07 14:04:28 EST 2011', 
			'privateCustomer' => false, 
		], $payload->getValue());
	}
}
