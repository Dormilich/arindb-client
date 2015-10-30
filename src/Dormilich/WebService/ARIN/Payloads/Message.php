<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;
use Dormilich\WebService\ARIN\Elements\GroupElement;
use Dormilich\WebService\ARIN\Elements\MultilineElement;

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
		$this->create(new MultilineElement('text'));
		$types = ['NONE', 'JUSTIFICATION'];
		$this->create(new FixedElement('category', $types));
		$this->create(new GroupElement('attachments'));
	}
}
