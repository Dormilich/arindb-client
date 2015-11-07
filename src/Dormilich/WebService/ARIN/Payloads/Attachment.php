<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;

/**
 * This payload allows you to add attachments to an existing Ticket as part of 
 * an Add Message call. 
 * 
 * This Attachment Payload should not be submitted by itself.
 */
class Attachment extends Payload
{
	public function __construct()
	{
		$this->name = 'attachment';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('data'));
		$this->create(new Element('filename'));
	}

	public function toXML()
	{
		throw new \LogicException('This Attachment Payload should not be submitted by itself.');
	}
}
