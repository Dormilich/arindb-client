<?php

namespace Dormilich\WebService\ARIN\Payloads;

class TicketedRequest extends Payload
{
	public function __construct()
	{
		$this->name = 'ticketedRequest';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Ticket);
		$this->create(new Net);
	}
}
