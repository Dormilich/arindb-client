<?php

namespace Dormilich\WebService\ARIN\Elements;

/**
 * This class represents an XML element that represents a boolean value.
 */
class Boolean extends Element
{
	/**
	 * Get the boolean content of the element.
	 * 
	 * @return boolean
	 */
	public function getValue()
	{
		return $this->value === 'true';
	}

	/**
	 * Convert input into boolean text.
	 * 
	 * @param mixed $value 
	 * @return boolean
	 */
	protected function convert($value)
	{
		if (filter_var($value, \FILTER_VALIDATE_BOOLEAN)) {
			return 'true';
		}
		return 'false';
	}
}
