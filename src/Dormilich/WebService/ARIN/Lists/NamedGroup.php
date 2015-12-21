<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Exceptions\ConstraintException;

/**
 * This class accepts any serialisable object(s) as its content that match the 
 * predefined tag names.
 * 
 * In the current ARIN API there is no case where a tag has child tags of 
 * variable names, which are not payloads. So the multiple name constraint is 
 * just for extensibility.
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
		throw new \LogicException(sprintf($msg, gettype($value), $this->getName()));
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
		// check if it has one of the allowed names
		if ($this->supports($value)) {
			return $value;
		}
		$msg = 'Object with name [%s] is not allowed in the [%s] element.';
		throw new ConstraintException(sprintf($msg, $value->getName(), $this->getName()));
	}

	/**
	 * Check if the value’s name is supported.
	 * 
	 * @param XMLHandler $value 
	 * @return boolean
	 */
	public function supports(XMLHandler $value)
	{
		return in_array($value->getName(), $this->nameList, true);
	}

	/**
	 * Providing a shortcut to add an element to the group. Named groups are 
	 * usually used only with Elements since they’re created on-the-fly. 
	 * 
	 * If there is only one name registered the name parameter may be omitted.
	 * 
	 * @param string $name (optional) Element name. 
	 * @param mixed $value Element value.
	 * @return self
	 */
	public function addElement($input)
	{
		if (func_num_args() === 2) {
			$value = func_get_arg(1);
			$this->addValue(Element::createWith($input, $value));

			return $this;
		}

		if (count($this->nameList) > 1) {
			$msg = 'Could not determine the name to instantiate the element with.';
			trigger_error($msg, \E_USER_WARNING);
		}
		elseif (func_num_args() === 1) {
			$name = $this->nameList[0];
			$this->addValue(Element::createWith($name, $input));
		}

		return $this;
	}
}
