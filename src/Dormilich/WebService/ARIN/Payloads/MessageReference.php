<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Lists\Group;

/**
 * This payload is a nested element of a Ticket Payload returned when a Get 
 * Ticket Details call is performed and the msgRefs parameter is specified as 
 * 'true'. You can then request a Get Message call with a specified MessageID, 
 * and will be returned a MessagePayload. 
 * 
 * This MessageReferencePayload should not be submitted by itself.
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
		$this->create(new Group('attachmentReferences'));
	}

	public function toXML()
	{
		throw new \Exception('This Attachment Payload should not be submitted by itself.');
	}
}
