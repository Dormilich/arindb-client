<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\ConstraintException;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class represents an XML element that represents a boolean value.
 */
class Boolean extends Element
{
	/**
	 * Get the boolean content of the element. Returns NULL if the value has 
	 * not been set.
	 * 
	 * @return boolean|NULL
	 */
	public function getValue()
	{
		if ($this->value === 'true') {
			return true;
		}
		if ($this->value === 'false') {
			return false;
		}
		return NULL;
	}

	/**
	 * Convert input into boolean text.
	 * 
	 * @param mixed $value 
	 * @return boolean
	 * @throws DataTypeException Value not stringifiable.
	 * @throws ConstraintException Validation failure.
	 */
	protected function convert($value)
	{
		$value = parent::convert($value);

		if ($this->validate($value)) {
			return 'true';
		}
		return 'false';
	}

	/**
	 * Validate the input value against a validation function.
	 * 
	 * @param mixed $value Input value.
	 * @return boolean Boolean equivalent of the input value.
	 * @throws ConstraintException Validation failure.
	 */
	protected function validate($value)
	{
		$bool = filter_var($value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);

		if (!is_bool($bool)) {
			$msg = 'Value [%s] is not a boolean value for the [%s] element.';
			$type = is_scalar($value) ? $value : gettype($value);
			throw new ConstraintException(sprintf($msg, $type, $this->getName()));
		}

		return $bool;
	}
}
