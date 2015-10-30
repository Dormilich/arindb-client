<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\GroupElement;

class MessageReference extends Payload
{
	public function __construct()
	{
		$this->name = 'messageReference';
		$this->init();
	}

	protected function init()
	{
		$this->create(new GroupElement('attachmentReferences'));
	}
}
