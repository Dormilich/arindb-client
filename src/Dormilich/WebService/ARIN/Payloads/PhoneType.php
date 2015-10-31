<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\ElementInterface;
use Dormilich\WebService\ARIN\Elements\FixedElement;

/**
 * This represents a phone type. It is a nested element of Phone Payload and 
 * should not be submitted by itself. The description field will be 
 * automatically filled in using the information in the code field, and should 
 * be left blank. 
 */
class PhoneType extends Payload implements ElementInterface
{
	public function __construct()
	{
		$this->name = 'type';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('description'));
		$this->create(new FixedElement('code', ['O', 'F', 'M']));
	}

	public function isDefined()
	{
		return $this->getElement('code')->isDefined();
	}

	public function getValue()
	{
		return array_map(function ($e) {
			return $e->getValue();
		}, $this->elements);
	}

	public function setValue($value)
	{
		$value = (string) $value;

		if (in_array($value, ['O', 'F', 'M'])) {
			$this->getElement('code')->setValue($value);
		}

		$this->getElement('description')->setValue($value);	

		return $this;	
	}

	public function addValue($value)
	{
		return $this->setValue($value);
	}

	public function toXML()
	{
		throw new \Exception('This Attachment Payload should not be submitted by itself.');
	}
}
