<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\XMLHandler;

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

	public function toXML($encoding = 'UTF-8', $validate = XMLHandler::VALIDATE)
	{
		throw new \LogicException('This Attachment Payload should not be submitted by itself.');
	}
}
