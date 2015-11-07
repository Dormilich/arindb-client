<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Net;
use Dormilich\WebService\ARIN\Payloads\NetBlock;
use Test\Payload_TestCase;

class NetTest extends Payload_TestCase
{
	public function testAssignmentValidity()
	{
		$payload = new Net;
		$block = new NetBlock;

		$block['type']  = 'A';
		$block['start'] = '10.0.0.0';
		$block['cidr']  = 24;
		$payload['net'] = $block;

		$payload['netName']   = 'NETNAME';
		$payload['parentNet'] = 'PARENTNETHANDLE';
		$payload['customer']  = 'C12341234';

		$this->assertTrue($payload->isValid());
	}

	public function testModificationValidity()
	{
		$payload = new Net;
		$block = new NetBlock;

		$block['type']  = 'A';
		$block['start'] = '010.000.000.000';
		$block['end']   = '010.000.000.255';
		$block['cidr']  = 24;
		$block['description'] = 'DESCRIPTION';

		$payload['version']   = 4;
		$payload['created']   = 'Tue Jan 25 16:17:18 EST 2011';
		$payload['handle']    = 'NET-10-0-0-0-1';
		$payload['parentNet'] = 'PARENTNETHANDLE';
		$payload['org']       = 'ARIN';
		$payload['customer']  = 'C12341234';

		$payload['name']  = 'NETNAME';
		$payload['ASN'][] = Element::createWith('originAS', 'AS102');

		$this->assertFalse($payload->isValid());

		$payload['net'] = $block;

		$this->assertTrue($payload->isValid());
	}

	public function testSerialiseAssign()
	{
		$payload = new Net;
		$block = new NetBlock;

		$block['type']  = 'A';
		$block['start'] = '10.0.0.0';
		$block['cidr']  = 24;
		$payload['net'] = $block;

		$payload['netName']   = 'NETNAME';
		$payload['parentNet'] = 'PARENTNETHANDLE';
		$payload['org'] = 'ARIN';

		$this->assertTrue($payload->isValid());

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('net-request');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

	public function testParseXML()
	{
		$payload = new Net;
		$payload->parse($this->loadXML('net-response'));

		$this->assertSame([
			'version' => '4',
			'comment' => [
				'Line 1'
			],
			'registrationDate' => 'Tue Jan 25 16:17:18 EST 2011',
			'orgHandle' => 'ARIN',
			'handle' => 'NET-10-0-0-0-1',
			'netBlocks' => [[
				'type'         => 'A',
				'description'  => 'DESCRIPTION',
				'startAddress' => '10.0.0.0',
				'endAddress'   => '10.0.0.255',
				'cidrLength'   => 24,
			]],
			'customerHandle' => 'C12341234',
			'parentNetHandle' => 'PARENTNETHANDLE',
			'netName' => 'NETNAME',
			'originASes' => [
				'AS102'
			],
			'pocLinks' => [],
		], $payload->getValue());
	}
}
