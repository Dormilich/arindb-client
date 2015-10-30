<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\ElementInterface;
use Dormilich\WebService\ARIN\Elements\FixedElement;

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
		if ($this->getElement('code')->isDefined()) {
			return $this->getElement('code')->getValue();
		}
		return $this->getElement('description')->getValue();
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
}
