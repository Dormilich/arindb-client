<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\BoolElement;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Elements\MultilineElement;

class Customer extends Payload
{
	public function __construct()
	{
		$this->name = 'customer';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('customerName'), 'name');
		$this->create(new Country, 'country');
		$this->create(new Element('handle'));
		$this->create(new MultilineElement('streetAddress'), 'address');
		$this->create(new Element('city'));
		$this->create(new LengthElement('iso3166-2', 2), 'state');
		$this->create(new Element('postalCode'));
		$this->create(new MultilineElement('comment'));
		$this->create(new Element('parentOrgHandle'), 'org');
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new BoolElement('privateCustomer'), 'private');
	}
}
