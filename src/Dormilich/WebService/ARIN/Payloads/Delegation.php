<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\GroupElement;

class Delegation extends Payload
{
	public function __construct()
	{
		$this->name = 'delegation';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('name'));
		$this->create(new GroupElement('delegationKeys'));
		$this->create(new GroupElement('nameservers'));
	}
}
