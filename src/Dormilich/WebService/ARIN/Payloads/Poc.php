<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;
use Dormilich\WebService\ARIN\Elements\GroupElement;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Elements\MultilineElement;

class Poc extends Payload
{
	public function __construct()
	{
		$this->name = 'poc';
		$this->init();
	}

	protected function init()
	{
		$this->create(new LengthElement('iso3166-2', 2), 'state');
		$this->create(new Country, 'country');
		$this->create(new GroupElement('emails'));
		$this->create(new MultilineElement('streetAddress'), 'address');
		$this->create(new Element('city'));
		$this->create(new Element('postalCode'));
		$this->create(new MultilineElement('comment'));
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Element('handle'));
		$this->create(new FixedElement('contactType', ['PERSON', 'ROLE']));
		$this->create(new Element('companyName'));
		$this->create(new Element('firstName'));
		$this->create(new Element('middleName'));
		$this->create(new Element('lastName'));
		$this->create(new GroupElement('phones'));
	}
}
