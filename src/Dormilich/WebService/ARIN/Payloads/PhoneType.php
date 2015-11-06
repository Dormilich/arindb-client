<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Selection;

/**
 * This represents a phone type. It is a nested element of Phone Payload and 
 * should not be submitted by itself. The description field will be 
 * automatically filled in using the information in the code field, and should 
 * be left blank. 
 */
class PhoneType extends Payload
{
	public function __construct()
	{
		$this->name = 'type';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('description'));
		$this->create(new Selection('code', ['O', 'F', 'M']));
	}

	public function isValid()
	{
		return $this->get('code')->isValid();
	}

	public function toXML()
	{
		throw new \LogicException('This Phone Type Payload should not be submitted by itself.');
	}
}
