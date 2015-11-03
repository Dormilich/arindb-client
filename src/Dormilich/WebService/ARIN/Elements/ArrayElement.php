<?php

namespace Dormilich\WebService\ARIN\Elements;

/**
 * An ArrayElement represents an XML element that only contains nested XML 
 * elements, but no text itself. This class’ decendents need to re-implement 
 * the toDOM() method.
 */
class ArrayElement extends Element implements \ArrayAccess
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

		if (NULL === $value) {
			return $this;
		}

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

	/**
	 * Check if the requested index exists.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param integer $offset Collection element index.
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->value[$offset]);
	}

	/**
	 * Get the requested element from the collection. Returns NULL if index 
	 * does not exist.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param integer $offset Collection element index.
	 * @return mixed Returns NULL if index does not exist.
	 */
	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset)) {
			return $this->value[$offset];
		}
		return NULL;
	}

	/**
	 * Set an element at the requested index. If the index is not found in the 
	 * collection, the value is appended instead.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param integer $offset Collection element index.
	 * @param mixed $value Replacement value.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		if ($this->offsetExists($offset)) {
			$this->value[$offset] = $this->convert($value);
		}
		else {
			$this->addValue($value);
		}
	}

	/**
	 * Remove the element at the requested position. The collection will be 
	 * re-indexed after the removal.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param integer $offset Collection element index.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		if ($this->offsetExists($offset)) {
			array_splice($this->value, $offset, 1);
		}
	}
}
