<?php

use Dormilich\WebService\ARIN\Payloads\Collection;
use Test\Payload_TestCase;

class CollectionTest extends Payload_TestCase
{
	public function testParseDelegation()
	{
		$payload = new Collection;
		$payload->parse($this->loadXML('list-delegation'));

		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Delegation', $payload[0]);
		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Delegation', $payload['delegation']);
	}

	public function testParseTicket()
	{
		$payload = new Collection;
		$payload->parse($this->loadXML('list-ticket'));

		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Ticket', $payload[0]);
		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Ticket', $payload['ticket']);
	}

	public function testParse()
	{
		$payload = new Collection;
		$payload->parse($this->loadXML('list-phone'));

		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Phone', $payload[0]);
		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Phone', $payload['phone']);
	}
}
