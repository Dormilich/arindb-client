<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\BoolElement;
use Dormilich\WebService\ARIN\Elements\GroupElement;

class Ticket extends Payload
{
	public function __construct()
	{
		$this->name = 'ticket';
		$this->init();
	}

	protected function init()
	{
		$uri = 'http://www.arin.net/regrws/shared-ticket/v1';

		$this->create(new GroupElement('messages'));
		$this->create(new Element('ticketNo'));
		$this->create(new BoolElement('ns4:shared', $uri));
		$this->create(new Element('ns4:orgHandle', $uri));
		$this->create(new Element('createdDate'));
		$this->create(new Element('resolvedDate'));
		$this->create(new Element('closedDate'));
		$this->create(new Element('updatedDate'));
		// the following attributes would be fixed, but not user-created
		$this->create(new Element('webTicketType'));
		$this->create(new Element('webTicketStatus'));
	}
}
