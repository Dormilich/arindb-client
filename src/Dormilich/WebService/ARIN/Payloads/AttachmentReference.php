<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Generated;
use Dormilich\WebService\ARIN\XMLHandler;

/**
 * This payload is contained within a MessagePayload returned during a Get 
 * Message call, or of a MessageReference Payload when a Get Ticket Details 
 * call is performed and the msgRefs parameter is specified as 'true'. 
 * 
 * This AttachmentReference Payload should not be submitted by itself.
 */
class AttachmentReference extends Payload
{
	public function __construct()
	{
		$this->name = 'attachmentReference';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('attachmentFilename'), 'filename');
		$this->create(new Generated('attachmentId'), 'id');
	}

	public function toXML($encoding = 'UTF-8', $validate = XMLHandler::VALIDATE)
	{
		throw new \LogicException('This Attachment Reference Payload should not be submitted by itself.');
	}
}
