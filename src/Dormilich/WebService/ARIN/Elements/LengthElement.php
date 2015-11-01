<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class represents an XML element that may only contain a string of a 
 * predefined length.
 */
class LengthElement extends Element
{
	/**
	 * @var integer $length Required string length.
	 */
	protected $length = 1;

	/**
	 * Set up the element defining the required content length.
	 * 
	 * @param string $name Tag name.
	 * @param integer $length Required content length.
	 * @return self
	 */
	public function __construct($name, $length)
	{
		parent::__construct($name);

		$this->length = filter_var($length, \FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1, 'default' => 1]
		]);
	}

	/**
	 * Check if the value conforms to the required string length.
	 * 
	 * Note: need to figure out if UTF is an issue here.
	 * 
	 * @param mixed $value 
	 * @return string
	 * @throws Exception Invalid string length found.
	 */
	protected function convert($value)
	{
		$value = parent::convert($value);
		if (strlen($value) === $this->length) {
			return $value;
		}
		$msg = 'Value "%s" does not match the expected length of %d for the [%s] element.';
		throw new DataTypeException(sprintf($msg, $value, $this->length, $this->name));
	}
}
