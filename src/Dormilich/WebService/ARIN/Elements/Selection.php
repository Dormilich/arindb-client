<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\ConstraintException;

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
     * @throws LogicException Namespace prefix missing.
	 * @throws LogicException Allowed value definition missing.
	 * @throws LogicException Allowed value definition empty.
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

		$this->setAllowed(end($args));
	}

	/**
	 * Set the allowed values. The values are cast to string.
	 * 
	 * @param array $values 
	 * @return void
	 * @throws LogicException Allowed value definition empty.
	 */
	protected function setAllowed(array $values)
	{
		foreach ($values as $value) {
			$this->allowed[] = (string) $value;
		}

		if (count($this->allowed) === 0) {
			throw new \LogicException('Allowed values list must not be empty.');
		}
	}

	/**
	 * Get the liast of allowed values.
	 * 
	 * @return array(string)
	 */
	public function getAllowed()
	{
		return $this->allowed;
	}

	/**
	 * Check if a value conforms to a list of allowed values using strict equality.
	 * 
	 * @param mixed $value 
	 * @return string
	 * @throws ConstraintException Value not allowed.
	 */
	protected function validate($value)
	{
		if (!in_array((string) $value, $this->allowed, true)) {
			$msg = 'Value "%s" is not allowed for the [%s] element.';
			throw new ConstraintException(sprintf($msg, $value, $this->getName()));
		}
		return $value;
	}
}
