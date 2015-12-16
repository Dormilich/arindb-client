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
		$this->assertEquals([[
			'messages' => [[
				'messageId' => 'MESSAGEID',
				'createdDate' => 'Tue Feb 28 17:41:17 EST 2012',
				'subject' => 'SUBJECT',
				'text' => [
					'Line 1'
				],
				'category' => 'NONE',
				'attachments' => [[
					'data' => 'DATA',
					'filename' => 'FILENAME',
				]],
			]],
			'ticketNo'      => 'TICKETNO',
			'createdDate'   => 'Tue Jan 25 16:17:18 EST 2011',
			'resolvedDate'  => 'Tue Jan 25 16:17:18 EST 2011',
			'closedDate'    => 'Tue Jan 25 16:17:18 EST 2011',
			'updatedDate'   => 'Tue Jan 25 16:17:18 EST 2011',
			'webTicketType'       => 'POC_RECOVERY',
			'webTicketStatus'     => 'PENDING_CONFIRMATION',
			'webTicketResolution' => 'ACCEPTED',
		]], $payload->getValue(true));
	}

	public function testParseNet()
	{
		$payload = new TicketedRequest;
		$payload->parse($this->loadXML('tr-net'));

		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $payload[0]);
		$this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $payload['net']);
		$this->assertEquals([[
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
		]], $payload->getValue(true));
	}
}
