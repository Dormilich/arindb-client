<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\GroupElement;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Elements\MultilineElement;

class Org extends Payload
{
	public function __construct()
	{
		$this->name = 'org';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Country, 'country');
		$this->create(new MultilineElement('streetAddress'), 'address');
		$this->create(new Element('city'));
		$this->create(new LengthElement('iso3166-2', 2), 'state');
		$this->create(new Element('postalCode'));
		$this->create(new MultilineElement('comment'));
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Element('handle'));
		$this->create(new Element('orgName'));
		$this->create(new Element('dbaName'));
		$this->create(new Element('taxId'));
		$this->create(new Element('orgUrl'));
		$this->create(new GroupElement('pocLinks'), 'poc');
	}
}
