<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Generated;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;
use Dormilich\WebService\ARIN\XMLHandler;

/**
 * This payload is a nested element of a Ticket Payload returned when a Get 
 * Ticket Details call is performed and the msgRefs parameter is specified as 
 * 'true'. You can then request a Get Message call with a specified MessageID, 
 * and will be returned a MessagePayload. 
 * 
 * This MessageReference Payload should not be submitted by itself.
 */
class MessageReference extends Payload
{
	public function __construct()
	{
		$this->name = 'messageReference';
		$this->init();
	}

	protected function init()
	{
		$this->create(new ObjectGroup('attachmentReferences', 'AttachmentReference'));
		$this->create(new Generated('messageId'), 'id');
	}

	public function isValid()
	{
		return $this->get('messageId')->isValid();
	}

	public function toXML($encoding = 'UTF-8', $validate = XMLHandler::VALIDATE)
	{
		throw new \LogicException('This Message Reference Payload should not be submitted by itself.');
	}
}
