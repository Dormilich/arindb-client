<?php

namespace Dormilich\WebService\ARIN\Elements;

/**
 * This class represents an XML element that may only contain previously 
 * specified string values.
 */
class FixedElement extends Element
{
	/**
	 * @var array(string) $allowed List of allowed values.
	 */
	protected $allowed = [];

	/**
	 * Set up the element defining the allowed values.
	 * 
	 * @param string $name Tag name.
	 * @param array(string) $allowed Allowed values.
	 * @return self
	 * @throws Exception An allowed value is not a string.
	 */
	public function __construct($name, array $allowed)
	{
		parent::__construct($name);

		foreach ($allowed as $value) {
			$this->allowed[] = parent::convert($value);
		}
	}

	/**
	 * Check if a value conforms to a list of allowed values using strict equality.
	 * 
	 * @param mixed $value 
	 * @return string
	 * @throws Exception Value not allowed.
	 */
	protected function convert($value)
	{
		$value = parent::convert($value);
		if (in_array($value, $this->allowed, true)) {
			return $value;
		}
		$msg = 'Value "%s" is not allowed for the [%s] element.';
		throw new \Exception(sprintf($msg, $value, $this->name));
	}
}
