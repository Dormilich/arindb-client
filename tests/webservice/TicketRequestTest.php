<?php

use Dormilich\WebService\ARIN\TicketRWS;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Ticket;
use Dormilich\WebService\ARIN\Payloads\Message;
use Dormilich\WebService\ARIN\Payloads\Net;
use Dormilich\WebService\ARIN\Payloads\NetBlock;
use Test\Payload_TestCase;

class TicketRequestTest extends Payload_TestCase
{
	public function testServiceDefaultsToTestDatabase()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$this->assertFalse($arin->isProduction());
	}

	public function testSetServiceToTestDatabase()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client, ['environment' => 'test']);

		$this->assertFalse($arin->isProduction());
	}

	public function testSetServiceToProductionDatabase()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client, ['environment' => 'live']);

		$this->assertTrue($arin->isProduction());
	}

	public function testAsnReportFromString()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->report('AS123');

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/report/whoWas/asn/AS123?apikey=', $client->url);
	}

	public function testAsnReportFromElement()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->report(Element::createWith('originAS', 'AS123'));

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/report/whoWas/asn/AS123?apikey=', $client->url);
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\RequestException
	 */
	public function testAsnReportFromInvalidElementFails()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->report(Element::createWith('test', 'AS123'));
	}

	public function testAssociationReport()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->report();

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/report/associations?apikey=', $client->url);
	}

	public function testNetReportWithIPv4()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->report('192.168.2.1');

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/report/whoWas/net/192.168.2.1?apikey=', $client->url);
	}

	public function testNetReportWithIPv6()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->report('2001:db8::1428:57ab');

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/report/whoWas/net/2001:db8::1428:57ab?apikey=', $client->url);
	}

	public function testNetReportWithNetBlock()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$address = new NetBlock;
		$address['start'] = '192.168.2.0';
		$address['end'] = '192.168.2.255';

		$arin->report($address);

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/report/whoWas/net/192.168.2.0?apikey=', $client->url);
	}

	public function testReassignmentReport()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->report(new Net('NET-HANDLE'));

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/report/reassignment/NET-HANDLE?apikey=', $client->url);
	}

	public function testGetTicketSummary()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->summary(new Ticket('12345'));

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/12345/summary?apikey=', $client->url);
	}

	public function testGetTicketDetails()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->read(new Ticket('12345'));

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/12345?apikey=&msgRefs=false', $client->url);
	}

	public function testGetTicketDetailsWithRefs()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->read(new Ticket('12345'), true);

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/12345?apikey=&msgRefs=true', $client->url);
	}

	public function testCloseTicket()
	{
		$client = $this->getClient('ticket-msgref');
		$arin = new TicketRWS($client);

		$ticket = $arin->read(new Ticket('TICKETNO'));
		$ticket['status'] = 'CLOSED';

		$arin->update($ticket, true);

		$this->assertSame('PUT', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/TICKETNO?apikey=&msgRefs=true', $client->url);
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ParserException
	 */
	public function testUpdateTicketWithInvalidStatusFails()
	{
		$client = $this->getClient('ticket-msgref');
		$arin = new TicketRWS($client);

		$ticket = $arin->read(new Ticket('12345'));

		$this->assertNotEquals('CLOSED', $ticket['status']->getValue());

		$arin->update($ticket);
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ParserException
	 */
	public function testCloseTicketWithoutPrefetchFails()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$ticket = new Ticket('TICKETNO');
		$ticket['status'] = 'CLOSED';

		$arin->update($ticket);
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\RequestException
	 */
	public function testSearchTicketWithoutConstraintsFails()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->search(false, false);
	}

	public function testSearchTicketWithType()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->search('ASN_REQUEST', false);

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket;ticketType=ASN_REQUEST?apikey=', $client->url);
	}

	public function testSearchTicketWithStatus()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->search(false, 'ABANDONED');

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket;ticketStatus=ABANDONED?apikey=', $client->url);
	}

	public function testSearchTicketWithTypeAndStatus()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->search('ASN_REQUEST', 'ABANDONED');

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket;ticketType=ASN_REQUEST;ticketStatus=ABANDONED?apikey=', $client->url);
	}

	public function testSearchTicketSummaryWithTypeAndStatus()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$arin->search('ASN_REQUEST', 'ABANDONED', true);

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/summary;ticketType=ASN_REQUEST;ticketStatus=ABANDONED?apikey=', $client->url);
	}

	public function testAddTicketMessage()
	{
		$client = $this->getClient();
		$arin = new TicketRWS($client);

		$ticket = new Ticket('TICKETNO');
		$message = new Message;
		$message['subject']  = 'test';
		$message['text']     = 'this is a simple test';
		$message['category'] = 'NONE';

		$arin->addMessage($ticket, $message);

		$this->assertSame('PUT', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/TICKETNO/message?apikey=', $client->url);
	}

	public function testGetTicketMessage()
	{
		$client = $this->getClient('ticket-msgref');
		$arin = new TicketRWS($client);

		$ticket = $arin->read(new Ticket('TICKETNO'));

		$arin->getMessage($ticket, 0);

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/TICKETNO/message/MESSAGEID?apikey=', $client->url);
	}

	public function testGetTicketAttachment()
	{
		$client = $this->getClient('ticket-msgref');
		$arin = new TicketRWS($client);

		$ticket = $arin->read(new Ticket('TICKETNO'));

		$arin->getAttachment($ticket, 0, 0);

		$this->assertSame('GET', $client->method);
		$this->assertSame('https://reg.ote.arin.net/rest/ticket/TICKETNO/message/MESSAGEID/attachment/ATTACHMENTID?apikey=', $client->url);
	}
}
