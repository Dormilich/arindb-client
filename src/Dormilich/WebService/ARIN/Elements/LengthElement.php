<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\ConstraintException;

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
	 * @param string $ns (optional) Namespace URI.
	 * @param integer $length Content length. Defaults to 1.
	 * @return self
     * @throws LogicException Namespace prefix missing.
	 */
	public function __construct($name, $ns)
	{
        $this->setNamespace((string) $name, $ns);

		$args = array_slice(func_get_args(), 1, 2);

		if ($this->namespace) {
			array_shift($args);
		}

		$this->setLength(end($args));
	}

	/**
	 * Set the value#s length constraint. Defaults to 1.
	 * 
	 * @param mixed $length 
	 * @return void
	 */
	protected function setLength($length)
	{
		$this->length = filter_var($length, \FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1, 'default' => 1]
		]);
	}

	/**
	 * Get the length constraint.
	 * 
	 * @return integer
	 */
	public function getLength()
	{
		return $this->length;
	}

	/**
	 * Check if the value conforms to the required string length.
	 * 
	 * Note: need to figure out if UTF is an issue here.
	 * 
	 * @param mixed $value 
	 * @return string
	 * @throws ConstraintException Invalid string length found.
	 */
	protected function validate($value)
	{
		if (strlen($value) !== $this->length) {
			$msg = 'Value "%s" does not match the expected length of %d for the [%s] element.';
			throw new ConstraintException(sprintf($msg, $value, $this->length, $this->getName()));
		}
		return $value;
	}
}
