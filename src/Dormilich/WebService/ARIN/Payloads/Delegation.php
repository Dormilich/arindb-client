<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Primary;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\NamedGroup;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;

/**
 * The Delegation Payload allows you to define the details of a Delegation, 
 * including nameservers and Delegation Signer (DS) keys.
 * 
 * The name field is automatically generated after you submit the payload, 
 * and should be left blank.
 */
class Delegation extends Payload implements Primary
{
	public function __construct($handle = NULL)
	{
		$this->name = 'delegation';
		$this->init();
		$this->set('name', $handle);
	}

	protected function init()
	{
		$this->create(new Element('name'));
		$this->create(new ObjectGroup('delegationKeys', 'DelegationKey'));
		$this->create(new NamedGroup('nameservers', 'nameserver'));
	}

	public function getHandle()
	{
		return $this->get('name')->getValue();
	}

	// not sue about the keys, though
	public function isValid()
	{
		return   $this->get('nameservers')->isValid()
			and !$this->get('name')->isValid();
	}

	public function __toString()
	{
		return (string) $this->get('name');
	}
}
