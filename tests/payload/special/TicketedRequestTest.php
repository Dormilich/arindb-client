<?php

use Dormilich\WebService\ARIN\Payloads\TicketedRequest;
use Test\Payload_TestCase;

class TicketedRequestTest extends Payload_TestCase
{
	public function testParseTicket()
	{
		$payload = new TicketedRequest;
		$payload->parse($this->loadXML('tr-ticket'));

		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Ticket', $payload[0]);
		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Ticket', $payload['ticket']);
	}

	public function testParseNet()
	{
		$payload = new TicketedRequest;
		$payload->parse($this->loadXML('tr-net'));

		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $payload[0]);
		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $payload['net']);
	}
}
