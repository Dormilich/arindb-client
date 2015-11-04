<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class represents an XML element that may only contain previously 
 * specified string values.
 */
class Selection extends Element
{
	/**
	 * @var array(string) $allowed List of allowed values.
	 */
	protected $allowed = [];

	/**
	 * Set up the element defining the allowed values.
	 * 
	 * @param string $name Tag name.
	 * @param string $ns (optional) Namespace URI.
	 * @param array(string) $allowed Allowed values.
	 * @return self
	 * @throws DataTypeException An allowed value is not a string.
	 * @throws LogicException Allowed value definition missing.
	 * @throws LogicException Allowed value definition empty.
     * @throws LogicException Namespace prefix missing.
	 */
	public function __construct($name, $ns)
	{
        $this->setNamespace((string) $name, $ns);

		$args = array_slice(func_get_args(), 1, 2);

		if ($this->namespace) {
			array_shift($args);
		}

		if (count($args) === 0) {
			throw new \LogicException('Allowed values are not defined.');
		}

		foreach ((array) end($args) as $value) {
			$this->allowed[] = parent::convert($value);
		}

		if (count($this->allowed) === 0) {
			throw new \LogicException('Allowed values list must not be empty.');
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
		throw new DataTypeException(sprintf($msg, $value, $this->name));
	}
}
