<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Integer;
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
class Country extends Payload
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
		$this->create(new Integer('e164', 1, 999));
	}

	public function isValid()
	{
		return $this->get('code2')->isValid()
			or $this->get('code3')->isValid();
	}

	public function toXML()
	{
		throw new \LogicException('This Country Payload should not be submitted by itself.');
	}
}
