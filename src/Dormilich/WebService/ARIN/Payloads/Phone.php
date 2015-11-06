<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;

/**
 * The Phone Payload is used by the POC Payload and as a standalone structure 
 * by the Add Phone call.
 * 
 * The number field should be in NANP format if applicable. The extension 
 * field is optional and can be left blank or not included in the payload you 
 * submit.
 */
class Phone extends Payload
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
}
