<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Boolean;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Lists\MultiLine;

/**
 * The Customer Payload contains details about a Customer.
 * 
 * The main difference between the ORG Payload and Customer Payload is the 
 * privateCustomer field. If true, the name and address fields will not be 
 * publicly visible when the ORG is displayed. If false or not provided, the 
 * Customer will be visible as if it were an ORG. Additionally, the Customer 
 * Payload does not have a dbaName, taxId,or  orgUrl field, nor does it have 
 * any related POCs.
 * 
 * The comment field can be used to display operational information about the 
 * Customer (NOC hours, website, etc.). All comments must be accurate and 
 * operational in nature. ARIN reserves the right to edit or remove public 
 * comments.
 * 
 * The parentOrgHandle field must contain the handle of the ORG from which 
 * this Customer has been reallocated/reassigned resources.
 * 
 * The following fields are automatically filled in once you submit the 
 * payload, and should be left blank:
 *  - handle
 *  - registrationDate
 * 
 * When performing a modify, if you include these fields with a different 
 * value from the original, omit them entirely, or leave them blank, it will 
 * return an error.
 */
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
		$this->create(new MultiLine('streetAddress'), 'address');
		$this->create(new Element('city'));
		$this->create(new LengthElement('iso3166-2', 2), 'state');
		$this->create(new Element('postalCode'));
		$this->create(new MultiLine('comment'));
		$this->create(new Element('parentOrgHandle'), 'org');
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Boolean('privateCustomer'), 'private');
	}
}
