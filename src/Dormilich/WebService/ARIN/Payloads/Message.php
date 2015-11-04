<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;
use Dormilich\WebService\ARIN\Lists\Group;
use Dormilich\WebService\ARIN\Lists\MultiLine;

/**
 * This payload allows the sending of additional information to an existing 
 * Ticket and to enable users to get a specific message and any accompanying 
 * attachment(s). The body of the payload will vary depending on the action 
 * requested.
 * 
 * The following fields are automatically completed by Reg-RWS, and should be 
 * left blank:
 *  - messageId
 *  - createdDate
 */
class Message extends Payload
{
	public function __construct()
	{
		$this->name = 'message';
		$this->init();
	}

	protected function init()
	{
		$uri = 'http://www.arin.net/regrws/messages/v1';
		$this->create(new Element('ns2:messageId', $uri));
		$this->create(new Element('ns2:createdDate', $uri));
		$this->create(new Element('subject'));
		$this->create(new MultiLine('text'));
		$types = ['NONE', 'JUSTIFICATION'];
		$this->create(new FixedElement('category', $types));
		$this->create(new Group('attachments'));
	}
}
