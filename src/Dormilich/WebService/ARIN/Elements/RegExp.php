<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\ConstraintException;

/**
 * This class represents an XML element that may only contain previously 
 * specified string values.
 */
class RegExp extends Element
{
	/**
	 * @var array(string) $allowed List of allowed values.
	 */
	protected $pattern;

	/**
	 * Set up the element defining the allowed values.
	 * 
	 * @param string $name Tag name.
	 * @param string $ns (optional) Namespace URI.
	 * @param array(string) $allowed Allowed values.
	 * @return self
	 * @throws LogicException Namespace prefix missing.
	 * @throws LogicException Pattern definition missing.
	 * @throws LogicException RegExp failed the validation test.
	 */
	public function __construct($name, $ns)
	{
		$this->setNamespace((string) $name, $ns);

		$args = array_slice(func_get_args(), 1, 2);

		if ($this->namespace) {
			array_shift($args);
		}

		if (count($args) === 0) {
			throw new \LogicException('Regular expression pattern is not defined.');
		}

		$this->setPattern(end($args));
	}

	/**
	 * Set and validate the regular expression.
	 * 
	 * @param string $regexp 
	 * @return void
	 * @throws LogicException RegExp failed the validation test.
	 */
	protected function setPattern($regexp)
	{
		$this->pattern = (string) $regexp;

		set_error_handler(function ($code, $message) {
			restore_error_handler();
			throw new \LogicException($message, $code);
		});
		preg_match($this->pattern, '');
		restore_error_handler();
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
		if (!preg_match($this->pattern, $value)) {
			$msg = 'Value "%s" is not allowed for the [%s] element.';
			throw new ConstraintException(sprintf($msg, $value, $this->getName()));
		}
		return $value;
	}
}
