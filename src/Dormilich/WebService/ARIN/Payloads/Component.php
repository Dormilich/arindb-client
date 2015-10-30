<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;

class Component extends Payload implements DOMSerializable
{
	public function __construct()
	{
		$this->name = 'component';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('name'));
		$this->create(new Element('message'));
	}

	public function isDefined()
	{
		return  $this->elements['name']->isDefined()
			and $this->elements['message']->isDefined()
		;
	}
}
