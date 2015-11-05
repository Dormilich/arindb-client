<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;

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
		$this->create(new Element('attachmentId'), 'id');
	}

	/**
	 * This is only an educated guess about the validity constraints.
	 */
	public function isValid()
	{
		return  $this->get('filename')->isDefined()
			and $this->get('id')->isDefined()
		;
	}

	public function toXML()
	{
		throw new \LogicException('This Attachment Reference Payload should not be submitted by itself.');
	}
}
