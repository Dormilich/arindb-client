<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Primary;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;

/**
 * The ORG Payload provides details about an organization, including their 
 * address and contact information.
 * 
 * The main difference between the ORG Payload and Customer Payload is the 
 * privateCustomer field, which an ORG Payload does not contain.
 * 
 * The comment field can be used to display operational information about the 
 * Customer (NOC hours, website, etc.). All comments must be accurate and 
 * operational in nature. ARIN reserves the right to edit or remove public 
 * comments.
 * 
 * The following fields are automatically filled in once you submit the 
 * payload, and should be left blank:
 *  - handle
 *  - registrationDate
 * 
 * The following fields may not be modified:
 *  - orgName
 *  - dbaName
 * 
 * If you alter, modify, or omit these fields when performing a ORG Modify, 
 * you will receive an error.
 * 
 * The element name orgURL is meant for a Referral Whois (RWhois) server 
 * hostname and port, not for the URL of the company's website. RWhois is a 
 * protocol typically run on port 4321 and is described in RFC 2167.
 * 
 * For information on the pocLinks field, see the POC Link Payload.
 * 
 * The iso-3166-1 field refers to an international standard for country codes. 
 * More information is available at: http://en.wikipedia.org/wiki/ISO_3166-1.
 * 
 * The iso-3166-2 refers to an international standard for state, province, 
 * county, or other relevant subdivisions as defined by each country. 
 * More information is available at: http://en.wikipedia.org/wiki/ISO_3166-2
 * 
 *  - ISO-3166-1 is mandatory for all ORGs.
 *  - ISO-3166-2 is required for U.S. and Canada.
 */
class Org extends Payload implements Primary
{
	public function __construct($handle = NULL)
	{
		$this->name = 'org';
		$this->init();
		$this->set('handle', $handle);
	}

	protected function init()
	{
		$this->create(new Country, 'country');
		$this->create(new MultiLine('streetAddress'), 'address');
		$this->create(new Element('city'));
		$this->create(new LengthElement('iso3166-2', 2), 'state');
		$this->create(new Element('postalCode'));
		$this->create(new MultiLine('comment'));
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Element('handle'));
		$this->create(new Element('orgName'));
		$this->create(new Element('dbaName'));
		$this->create(new Element('taxId'));
		$this->create(new Element('orgUrl'));
		$this->create(new ObjectGroup('pocLinks', 'PocLinkRef'), 'poc');
	}

	public function getHandle()
	{
		return $this->get('handle')->getValue();
	}

	public function isValid()
	{
		// there’s not much in the docs…
		$handle = $this->get('handle')->isValid();
		$date   = $this->get('created')->isValid();

		return !($handle xor $date);
	}

	public function __toString()
	{
		return (string) $this->get('orgName');
	}
}
