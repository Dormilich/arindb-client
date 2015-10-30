<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;

class DelegationKey extends Payload implements DOMSerializable
{
	public function __construct()
	{
		$this->name = 'delegationKey';
		$this->init();
	}

	protected function init()
	{
		$this->create(new FixedElement('algorithm', ['5', '7', '8']));
		$this->create(new Element('digest'));
		$this->create(new Element('ttl'));
		$this->create(new FixedElement('digestType', ['1', '2']));
		$this->create(new Element('keyTag'));
	}

	public function isDefined()
	{
		return  $this->elements['algorithm']->isDefined()
			and $this->elements['digest']->isDefined()
			and $this->elements['ttl']->isDefined()
			and $this->elements['digestType']->isDefined()
			and $this->elements['keyTag']->isDefined()
		;
	}
}
