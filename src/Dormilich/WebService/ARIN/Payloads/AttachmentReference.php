<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;

/**
 * This payload is contained within a MessagePayload returned during a Get 
 * Message call, or of a MessageReference Payload when a Get Ticket Details 
 * call is performed and the msgRefs parameter is specified as 'true'. 
 * 
 * This AttachmentReference Payload should not be submitted by itself.
 */
class AttachmentReference extends Payload implements DOMSerializable
{
	public function __construct()
	{
		$this->name = 'attachmentReference';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('attachmentFilename'));
		$this->create(new Element('attachmentId'));
	}

	public function isDefined()
	{
		return  $this->elements['attachmentFilename']->isDefined()
			and $this->elements['attachmentId']->isDefined()
		;
	}
}
