<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\GroupElement;

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
		// though it’s originally restricted, it’s not
		// an attribute that is set by the user
		$this->create(new Element('code'));
		$this->create(new GroupElement('components'));
		$this->create(new GroupElement('additionalInfo'));
	}
}
