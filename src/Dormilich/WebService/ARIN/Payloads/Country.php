<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\ElementInterface;
use Dormilich\WebService\ARIN\Elements\LengthElement;

/**
 * The Country Payload identifies a country using two-digit, three-digit, 
 * and/or e164 codes.
 * 
 * The name and e164 (ITU-T E.164 international calling codes) fields are 
 * not required. Either the two-digit (code2) or three-digit (code3) code 
 * fields must be specified. If you specify both,they must match the same 
 * country.
 */
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
		return $this->get('code2')->isDefined() or 
			$this->get('code3')->isDefined();
	}

	public function setValue($value)
	{
		$value = strtoupper((string) $value);

		if (preg_match('~^[A-Z]{2}$~', $value)) {
			return $this->get('code2')->setValue($value);
		}

		if (preg_match('~^[A-Z]{3}$~', $value)) {
			return $this->get('code3')->setValue($value);
		}

		$int = filter_var(ltrim($value, '+0'), \FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1, 'max_range' => 998]
		]);
		if ($int) {
			return $this->get('e164')->setValue($int);
		}

		$this->get('name')->setValue($value);	

		return $this;	
	}

	public function addValue($value)
	{
		return $this->setValue($value);
	}
}
