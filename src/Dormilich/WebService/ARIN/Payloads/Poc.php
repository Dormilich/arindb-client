<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Selection;
use Dormilich\WebService\ARIN\Lists\Group;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Lists\MultiLine;

/**
 * The POC Payload provides information about a POC.
 * 
 * The comment field can be used to display operational information about the 
 * Customer (NOC hours, website, etc.). All comments must be accurate and 
 * operational in nature. ARIN reserves the right to edit or remove public 
 * comments.
 * 
 * The following fields are automatically filled in once you submit the payload, and should be left blank:
 *  - handle
 *  - registrationDate
 * 
 * When performing a modify, if you include these fields with a different 
 * value from the original, omit them entirely, or leave them blank, it will 
 * return an error.
 * 
 * The following fields may not be modified:
 *  - contactType
 *  - firstName
 *  - middleName
 *  - lastName
 * 
 * If you alter, modify, or omit these fields when performing a POC Modify, 
 * you will receive an error.
 * 
 * The iso-3166-1 field refers to an international standard for country codes. 
 * More information is available at: http://en.wikipedia.org/wiki/ISO_3166-1.
 * 
 * The iso-3166-2 refers to an international standard for state, province, 
 * county, or other relevant subdivisions as defined by each country. 
 * More information is available at: http://en.wikipedia.org/wiki/ISO_3166-2
 * 
 *  - ISO-3166-1 is mandatory for all new POCs.
 *  - ISO-3166-2 is required for U.S. and Canada.
 * 
 *     Note: Each POC must have at least one Office Phone listed.
 */
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
		$this->create(new Group('emails'));
		$this->create(new MultiLine('streetAddress'), 'address');
		$this->create(new Element('city'));
		$this->create(new Element('postalCode'));
		$this->create(new MultiLine('comment'));
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Element('handle'));
		$this->create(new Selection('contactType', ['PERSON', 'ROLE']));
		$this->create(new Element('companyName'));
		$this->create(new Element('firstName'));
		$this->create(new Element('middleName'));
		$this->create(new Element('lastName'));
		$this->create(new Group('phones'));
	}
}
