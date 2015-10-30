<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\ElementInterface;
use Dormilich\WebService\ARIN\Elements\LengthElement;

class Country extends Payload implements ElementInterface
{
	public function __construct()
	{
		$this->name = 'iso3166-1';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('name'));
		$this->create(new LengthElement('code2', 2));
		$this->create(new LengthElement('code3', 3));
		$this->create(new Element('e164'));
	}

	public function isDefined()
	{
		return $this->elements['code2']->isDefined() or $this->elements['code3']->isDefined();
	}

	public function getValue()
	{
		if ($this->getElement('code2')->isDefined()) {
			return $this->getElement('code2')->getValue();
		}
		if ($this->getElement('code3')->isDefined()) {
			return $this->getElement('code3')->getValue();
		}
		return $this->getElement('name')->getValue();
	}

	public function setValue($value)
	{
		$value = strtoupper((string) $value);

		if (preg_match('~^[A-Z]{2}$~', $value)) {
			return $this->getElement('code2')->setValue($value);
		}

		if (preg_match('~^[A-Z]{3}$~', $value)) {
			return $this->getElement('code3')->setValue($value);
		}

		$int = filter_var(ltrim($value, '+0'), \FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1, 'max_range' => 998]
		]);
		if ($int) {
			return $this->getElement('e164')->setValue($int);
		}

		$this->getElement('name')->setValue($value);	

		return $this;	
	}

	public function addValue($value)
	{
		return $this->setValue($value);
	}
}
