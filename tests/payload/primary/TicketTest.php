<?php

use Dormilich\WebService\ARIN\Payloads\Ticket;
use Test\Payload_TestCase;

class TicketTest extends Payload_TestCase
{
	public function testParseMessageXML()
	{
		$payload = new Ticket;
		$payload->parse($this->loadXML('ticket-msg'));

		$this->assertSame([
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
				'attachmentReferences' => [],
			]],
			'messageReferences' => [],
			'ticketNo'      => 'TICKETNO',
			'shared'        => true,
			'orgHandle'     => 'ORGHANDLE',
			'createdDate'   => 'Mon Nov 07 14:04:29 EST 2011',
			'resolvedDate'  => 'Mon Nov 07 14:04:29 EST 2011',
			'closedDate'    => 'Mon Nov 07 14:04:29 EST 2011',
			'updatedDate'   => 'Mon Nov 07 14:04:29 EST 2011',
			'webTicketType'       => 'POC_RECOVERY',
			'webTicketStatus'     => 'PENDING_CONFIRMATION',
			'webTicketResolution' => 'ACCEPTED',
		], $payload->getValue());
	}

	public function testParseMessageRefXML()
	{
		$payload = new Ticket;
		$payload->parse($this->loadXML('ticket-msgref'));

		$this->assertSame([
			'messages' => [],
			'messageReferences' => [[
				'attachmentReferences' => [[
					'attachmentFilename' => 'ATTACHMENTFILENAME',
					'attachmentId' => 'ATTACHMENTID',
				]],
				'messageId' => 'MESSAGEID',
			]],
			'ticketNo'      => 'TICKETNO',
			'shared'        => NULL,
			'orgHandle'     => NULL,
			'createdDate'   => 'Tue Jan 25 16:17:18 EST 2011',
			'resolvedDate'  => 'Tue Jan 25 16:17:18 EST 2011',
			'closedDate'    => 'Tue Jan 25 16:17:18 EST 2011',
			'updatedDate'   => 'Tue Jan 25 16:17:18 EST 2011',
			'webTicketType'       => 'POC_RECOVERY',
			'webTicketStatus'     => 'PENDING_CONFIRMATION',
			'webTicketResolution' => 'ACCEPTED',
		], $payload->getValue());
	}

	public function testSerialise()
	{
		$payload = new Ticket;
		$payload->parse($this->loadXML('ticket-msgref'));

		$payload['status'] = 'CLOSED';
		unset($payload['messageReferences']);

		$this->assertTrue($payload->isValid());

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('ticket-status');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

}
