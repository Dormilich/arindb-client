<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\XMLHandler;

/**
 * Component Error Payloads represent individual component errors in the Error Payload. 
 */
class Component extends Payload
{
	public function __construct()
	{
		$this->name = 'component';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('name'));
		$this->create(new Element('message'));
	}

	public function toXML($encoding = 'UTF-8', $validate = XMLHandler::VALIDATE)
	{
		throw new \LogicException('This Component Error Payload should not be submitted by itself.');
	}
}
