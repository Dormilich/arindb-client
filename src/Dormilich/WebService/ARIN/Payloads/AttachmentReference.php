<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;

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
