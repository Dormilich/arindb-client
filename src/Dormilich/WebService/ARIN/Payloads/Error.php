<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;

/**
 * The Error Payload is returned when any call encounters errors and it 
 * contains the reason for the error.  
 */
class Error extends Payload
{
	public function __construct()
	{
		$this->name = 'error';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('message'));
		$this->create(new Element('code'));
		$this->create(new ObjectGroup('components', 'Component'));
		$this->create(new ObjectGroup('additionalInfo', 'Element'));
	}

	public function isValid()
	{
		return false;
	}

	public function toXML()
	{
		throw new \LogicException('This Error Payload should not be submitted at all.');
	}
}
