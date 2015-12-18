<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Primary;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Selection;
use Dormilich\WebService\ARIN\Elements\LengthElement;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Dormilich\WebService\ARIN\Lists\NamedGroup;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;

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
class Poc extends Payload implements Primary
{
	private $linked = true;

	public function __construct($handle = NULL)
	{
		$this->name = 'poc';
		$this->init();
		$this->set('handle', $handle);
	}

	protected function init()
	{
		$this->create(new LengthElement('iso3166-2', 1, 3), 'state');
		$this->create(new Country, 'country');
		$this->create(new NamedGroup('emails', 'email'));
		$this->create(new MultiLine('streetAddress'), 'address');
		$this->create(new Element('city'));
		$this->create(new Element('postalCode'));
		$this->create(new MultiLine('comment'));
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Element('handle'));
		$this->create(new Selection('contactType', ['PERSON', 'ROLE']), 'type');
		$this->create(new Element('companyName'), 'company');
		$this->create(new Element('firstName'));
		$this->create(new Element('middleName'));
		$this->create(new Element('lastName'));
		$this->create(new ObjectGroup('phones', 'Phone'));
	}

	public function getHandle()
	{
		return $this->get('handle')->getValue();
	}

	public function isValid()
	{
		$elements = $this->filter('contactType', 'country', 'streetAddress', 'city', 'emails', 'phones');
		$required = array_reduce($elements, function ($carry, $item) {
			return $carry and $item->isValid();
		}, true);

		if (!$required) {
			return false;
		}

		$first = $this->get('firstName')->isValid();
		$last  = $this->get('lastName')->isValid();

		if ($this->get('type')->getValue() === 'ROLE') {
			return $last and !$first and $this->get('company')->isValid();
		}
		if ($this->get('type')->getValue() === 'PERSON') {
			return $first and $last;
		}
		return false;
	}

	public function __toString()
	{
		if ($this->get('type')->getValue() === 'ROLE') {
			return sprintf('%s (%s)', $this->get('lastName'), $this->get('company'));
		}
		if ($this->get('type')->getValue() === 'PERSON') {
			return implode(' ', $this->filter('firstName', 'lastName'));
		}
		return (string) $this->get('handle');
	}

	/**
	 * Get the boolean value for the API request’s makeLink option. 
	 * If a parameter is passed to the function, this method is used as setter.
	 * 
	 * The default value is TRUE (link Poc to account).
	 * 
	 * @return boolean
	 */
	public function makeLink()
	{
		if (func_num_args() === 1) {
			$this->linked = filter_var(func_get_arg(0), \FILTER_VALIDATE_BOOLEAN);
		}
		return $this->linked;
	}
}
