<?php

use Dormilich\WebService\ARIN\Payloads\Org;
use Dormilich\WebService\ARIN\Payloads\PocLinkRef;
use Test\Payload_TestCase;

class OrgTest extends Payload_TestCase
{
	private function getPocLinks()
	{
		$ref1 = new PocLinkRef;
		$ref1->set('function', 'AD')->set('handle', 'ADMIN-ARIN');
		$ref2 = new PocLinkRef;
		$ref2->set('function', 'AB')->set('handle', 'ABUSE-ARIN');
		$ref3 = new PocLinkRef;
		$ref3->set('function', 'T')->set('handle', 'TECH-ARIN');

		return [$ref1, $ref2, $ref3];
	}

	public function testSerialise()
	{
		$payload = new Org;

		$payload['country']['name']  = 'UNITED STATES';
		$payload['country']['code2'] = 'US';
		$payload['country']['code3'] = 'USA';
		$payload['country']['e164']  = '1';
		$payload['address'] = 'Line 1';
		$payload['city'] = 'Chantilly';
		$payload['state'] = 'VA';
		$payload['postalCode'] = '20151';
		$payload['comment'] = 'Line 1';
		$payload['created'] = 'Mon Nov 07 14:04:28 EST 2011';
		$payload['handle'] = 'ARIN';
		$payload['orgName'] = 'ORGNAME';
		$payload['dbaName'] = 'DBANAME';
		$payload['taxId'] = 'TAXID';
		$payload['orgUrl'] = 'ORGURL';
		$payload['poc'] = $this->getPocLinks();

		$this->assertTrue($payload->isValid());

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('org');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

	public function testParseXML()
	{
		$payload = new Org;
		$payload->parse($this->loadXML('org'));

		$this->assertSame([
			'iso3166-1' => [
				'name'  => 'UNITED STATES',
				'code2' => 'US',
				'code3' => 'USA',
				'e164'  => 1,
			],
			'streetAddress' => ['Line 1'],
			'city' => 'Chantilly',
			'iso3166-2' => 'VA',
			'postalCode' => '20151',
			'comment' => ['Line 1'],
			'registrationDate' => 'Mon Nov 07 14:04:28 EST 2011',
			'handle'  => 'ARIN',
			'orgName' => 'ORGNAME',
			'dbaName' => 'DBANAME',
			'taxId'   => 'TAXID',
			'orgUrl'  => 'ORGURL',
			'pocLinks' => [[
				'description' => null,
				'handle' => 'ADMIN-ARIN',
				'function' => 'AD',
			], [
				'description' => null,
				'handle' => 'ABUSE-ARIN',
				'function' => 'AB',
			], [
				'description' => null,
				'handle' => 'TECH-ARIN',
				'function' => 'T',
			]],
		], $payload->getValue());
	}
}
