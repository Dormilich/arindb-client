<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\Group;

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
		$this->create(new Group('delegationKeys'));
		$this->create(new Group('nameservers'));
	}
}
