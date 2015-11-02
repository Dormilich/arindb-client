<?php

namespace Dormilich\WebService\ARIN\Elements;

/**
 * An ArrayElement represents an XML element that only contains nested XML 
 * elements, but no text itself. This class’ decendents need to re-implement 
 * the toDOM() method.
 */
class ArrayElement extends Element
{
	/**
	 * @var array $value Collection of the nested data.
	 */
	protected $value = [];

	/**
	 * Returns TRUE if the element’s data collection is not empty.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return count($this->value) > 0;
	}

	/**
	 * Discard the existing data and add the new content. A collection can 
	 * also be set using an array of coresponding data.
	 * 
	 * @param array|mixed $value Value item(s) to set.
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = [];

		if (is_array($value)) {
			foreach ($value as $item) {
				$this->addValue($item);
			}
		}
		else {
			$this->addValue($value);
		}

		return $this;
	}

	/**
	 * Add a single data item to the collection.
	 * 
	 * @param mixed $value 
	 * @return self
	 */
	public function addValue($value)
	{
		$this->value[] = $this->convert($value);

		return $this;
	}

	/**
	 * This class cannot represent a concrete XML element.
	 * 
	 * @param DOMDocument $doc 
	 * @throws LogicException
	 */
	public function toDOM(\DOMDocument $doc)
	{
		throw new \LogicException('Invalid data class.');
	}
}
