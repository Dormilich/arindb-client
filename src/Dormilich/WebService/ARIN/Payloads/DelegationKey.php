<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;

/**
 * The Delegation Key Payload is the portion of the Delegation Payload that 
 * contains algorithm name and digest type information.
 * 
 * The algorithm name and digest type name will be determined by the values 
 * you enter. You do not need to set the name on the payload. If you do, it 
 * will be discarded.
 */
class DelegationKey extends Payload
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
