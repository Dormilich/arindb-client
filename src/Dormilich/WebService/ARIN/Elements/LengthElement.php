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
	 * @var integer $length Required string minimum length.
	 */
	protected $min = 1;

	/**
	 * @var integer $length Required string maximum length.
	 */
	protected $max;

	/**
	 * Set up the element defining the required content length.
	 * 
	 * @param string $name Tag name.
	 * @param string $ns (optional) Namespace URI.
	 * @param integer $min Content minimum length. 
	 * @param integer $max Content maximum length. 
	 * @return self
     * @throws LogicException Namespace prefix missing.
	 */
	public function __construct($name, $ns)
	{
        $this->setNamespace((string) $name, $ns);

		$args = array_slice(func_get_args(), 1);

		if ($this->namespace) {
			array_shift($args);
		}

		call_user_func_array([$this, 'setLength'], $args);
	}

	/**
	 * Set the value’s length constraints. The minimum length is 1
	 * 
	 * @param mixed $length 
	 * @return void
	 */
	protected function setLength($min = 1, $max = NULL)
	{
		$iMin = filter_var($min, \FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
		$iMax = filter_var($max, \FILTER_VALIDATE_INT, ['options' => ['min_range' => max(1, $iMin)]]);

		if (NULL !== $min and false === $iMin) {
			throw new \LogicException('The setting for the minimum length must be an integer > 0 or NULL');
		}
		if (NULL !== $max and false === $iMax) {
			throw new \LogicException('The setting for the maximum length must be an integer > 0 or NULL');
		}

		$this->min = $iMin;
		$this->max = $iMax;
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
		$options = [];

		if ($this->min) {
			$options['min_range'] = $this->min;
		}
		if ($this->max) {
			$options['max_range'] = $this->max;
		}

		// no length restriction
		if (count($options) === 0) {
			return $value;
		}

		if (filter_var(strlen($value), \FILTER_VALIDATE_INT, ['options' => $options])) {
			return $value;
		}

		$msg = 'Value "%s" does not match the expected length range of {%s,%s} for the [%s] element.';
		throw new ConstraintException(sprintf($msg, $value, $this->min, $this->max, $this->getName()));
	}
}
