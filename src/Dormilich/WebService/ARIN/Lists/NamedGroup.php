<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\Exceptions\ConstraintException;

/**
 * This class accepts any serialisable object(s) as its content that match the 
 * predefined tag names.
 */
class NamedGroup extends Group
{
	/**
	 * @var array(string) $nameList List of allowed tag names of the collection elements.
	 */
	protected $nameList = [];

	/**
	 * Set the elements base name and the allowed tag names.
	 * 
	 * @param string $name Tag name.
	 * @param string|array(string) $nameList List of allowed names in the collection.
	 * @return self
	 * @throws LogicException A tag name is not a string.
	 * @throws LogicException Tag names definition empty.
	 */
	public function __construct($name, $nameList)
	{
		parent::__construct($name);

		$nameList = (array) $nameList;

		if (count($nameList) === 0) {
			throw new \LogicException('Allowed tag name list must not be empty.');
		}

		$this->nameList = array_map([$this, 'getTagName'], $nameList);
	}

	/**
	 * Convert the tag names into strings.
	 * 
	 * @param mixed $value Tag name.
	 * @return string Tag name.
	 * @throws LogicException A tag name is not a string.
	 */
	private function getTagName($value)
	{
		if (is_scalar($value) or (is_object($value) and method_exists($value, '__toString'))) {
			return (string) $value;
		}
		$msg = 'Value of type %s cannot be converted to a string for the [%s] element.';
		throw new \LogicException(sprintf($msg, gettype($value), $this->name));
	}

	/**
	 * Check if the value is a serialisable element and matches the defined tag names.
	 * 
	 * @param object $value 
	 * @return XMLHandler
	 * @throws ConstraintException Value has an invalid tag name.
	 */
	protected function convert($value)
	{
		// objects must implement XMLHandler
		$value = parent::convert($value);
		// check if it's one of the allowed classes
		if (in_array($value->getName(), $this->nameList, true)) {
			return $value;
		}
		$msg = 'Object with name [%s] is not allowed in the [%s] element.';
		throw new ConstraintException(sprintf($msg, $value->getName(), $this->name));
	}
}
