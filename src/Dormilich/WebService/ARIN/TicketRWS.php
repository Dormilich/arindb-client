<?php
// TicketRWS.php

namespace Dormilich\WebService\ARIN;

use Dormilich\WebService\ARIN\Exceptions\RequestException;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Payload;
use Dormilich\WebService\ARIN\Payloads\Net;
use Dormilich\WebService\ARIN\Payloads\NetBlock;
use Dormilich\WebService\ARIN\Payloads\Message;
use Dormilich\WebService\ARIN\Payloads\Ticket;

/**
 * Handle anything that is processed through tickets. That concerns ticket 
 * messages/attachments, reports, and ROA.
 */
class TicketRWS extends WebServiceSetup
{
	// reports

	/**
	 * Get history reports about certain objects.
	 *  - Association report: no param
	 *  - Net report: IP address or NetBlock
	 *  - Reassignment report: Net
	 *  - ASN report: originAS element (from Net) or string
	 * 
	 * @param mixed $param 
	 * @return Ticket
	 */
	public function report($param = NULL)
	{
		$path = 'report/';

		if (NULL === $param) {
			$path .= 'associations';
		}
		elseif (filter_var($param, \FILTER_VALIDATE_IP)) {
			$path .= 'whoWas/net/' . $param;
		}
		elseif ($param instanceof NetBlock) {
			$path .= 'whoWas/net/' . $param['start'];
		}
		elseif ($param instanceof Net) {
			$path .= 'reassignment/' . $param['handle'];
		}
		elseif ($param instanceof Element and $param->hasName('originAS')) {
			$path .= 'whoWas/asn/' . $param;
		}
		elseif (is_string($param) and strlen($param)) {
			$path .= 'whoWas/asn/' . $param;
		}
		else {
			throw new RequestException('Invalid parameter given.');
		}
		return $this->submit('GET', $path);
	}

	// tickets

	/**
	 * Get a ticket. The msgRefs parameter specifies, whether messages or 
	 * message references shall be returned.
	 * 
	 * @param Ticket $ticket 
	 * @param boolean $refs 
	 * @return Ticket
	 */
	public function read(Ticket $ticket, $refs = false)
	{
		$path = 'ticket/' . $ticket->getHandle();
		return $this->submit('GET', $path, [
			'msgRefs' => $this->bool2string($refs)
		]);
	}

	/**
	 * Get a ticket without messages.
	 * 
	 * @param Ticket $ticket 
	 * @return Ticket
	 */
	public function summary(Ticket $ticket)
	{
		$path = sprintf('ticket/%s/summary', $ticket->getHandle());
		return $this->submit('GET', $path);
	}

	/**
	 * Update the status (RESOLVED => CLOSED) of a ticket. The msgRefs 
	 * parameter specifies, whether messages or message references shall be 
	 * returned.
	 * 
	 * @param Ticket $ticket 
	 * @param boolean $refs 
	 * @return Ticket
	 */
	public function update(Ticket $ticket, $refs = false)
	{
		$path  = 'ticket/' . $ticket->getHandle();
		$path .= '?'.  http_build_query([
			'apikey'  => $this->config['password'], 
			'msgRefs' => $this->bool2string($refs),
		], '', '&', \PHP_QUERY_RFC3986);

		$headers = [
			'Content-Type' => 'application/xml',
			'Accept' => 'application/xml',
		];

        $body = $ticket->toXML($this->config['encoding'], $this->config['strict'])->saveXML();

		$xml = $this->client->request('PUT', $path, $headers, $body);

		return Payload::loadXML($xml);
	}

	/**
	 * Search for tickets by status and/or type. If the summary parameter is set 
	 * to TRUE, the tickets are returned without messages (ticket summaries).
	 * 
	 * @param string $type Ticket type.
	 * @param string $status Ticket status.
	 * @param boolean $summary Whether to omit messages.
	 * @return Collection|FALSE
	 */
	public function search($type, $status, $summary = false)
	{
		$path = 'ticket';

		if ($summary) {
			$path .= '/summary';
		}
		if ($type) {
			$path .= ';ticketType=' . strtoupper($type);
		}
		if ($status) {
			$path .= ';ticketStatus=' . strtoupper($status);
		}

		if (strpos($path, ';') === false) {
			throw new RequestException('Search constraints must not be empty.');
		}
		return $this->submit('GET', $path);
	}

	// messages

	/**
	 * Add a message/attachment to a ticket.
	 * 
	 * @param Ticket $ticket 
	 * @param Message $message 
	 * @return Message
	 */
	public function addMessage(Ticket $ticket, Message $message)
	{
		$path = sprintf('ticket/%s/message', $ticket->getHandle());
		return $this->submit('PUT', $path, [], $message);
	}

	/**
	 * Get a message from a specific ticket. It’s unlikely to know message and 
	 * ticket id without fetching the ticket beforehand, so this is essentially 
	 * fetching messages from previously fetched message references.
	 * 
	 * @param Ticket $ticket 
	 * @param integer $msgPos Position of the message in the ticket.
	 * @return Ticket
	 */
	public function getMessage(Ticket $ticket, $msgPos = 0)
	{
		$path = sprintf('ticket/%s/message/%s', $ticket->getHandle(), 
			$ticket['messageReferences'][$msgPos]['id']);
		return $this->submit('GET', $path);
	}

	/**
	 * Get an attachment from a specific ticket. It’s unlikely to know attachment, 
	 * message, and ticket id without fetching the ticket beforehand, so this is 
	 * essentially fetching attachments from previously fetched references.
	 * 
	 * @param Ticket $ticket 
	 * @param integer $msgPos Position of the message in the ticket.
	 * @param integer $attPos Position of the attachment in the message.
	 * @return <application/octet-stream>
	 */
	public function getAttachment(Ticket $ticket, $msgPos = 0, $attPos = 0)
	{
		$message = $ticket['messageReferences'][$msgPos];
		$attachment = $message['attachmentReferences'][$attPos];

		$path = sprintf('ticket/%s/message/%s/attachment/%s', $ticket->getHandle(), 
			$message['id'], $attachment['id']);
		return $this->submit('GET', $path);
	}
}
