<?php

namespace Dormilich\WebService\ARIN\Payloads;

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
		$this->create(new ObjectGroup('delegationKeys', 'DelegationKey'));
		$this->create(new NamedGroup('nameservers', 'nameserver'));
	}

	// not sue about the keys, though
	public function isValid()
	{
		return   $this->get('nameservers')->isValid()
			and !$this->get('name')->isValid();
	}
}
