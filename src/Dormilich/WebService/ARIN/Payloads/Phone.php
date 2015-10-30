<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;

class Phone extends Payload implements DOMSerializable
{
	public function __construct()
	{
		$this->name = 'phone';
		$this->init();
	}

	protected function init()
	{
		$this->create(new PhoneType);
		$this->create(new Element('number'));
		$this->create(new Element('extension'));
	}

	public function isDefined()
	{
		$type = $this->getAttribute('type')->isDefined();
		$num  = $this->getAttribute('number')->isDefined();

		return $type and $num;
	}
}
