<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\ElementInterface;
use Dormilich\WebService\ARIN\XMLHandler;

/**
 * An ArrayElement represents an XML element that only contains nested XML 
 * elements, but no text itself. This class’ decendents need to re-implement 
 * the toDOM() method.
 */
abstract class ArrayElement implements ElementInterface, XMLHandler, \ArrayAccess, \Countable
{
	/**
	 * @var string $name The element’s tag name.
	 */
	protected $name;

	/**
	 * @var array $value Collection of the nested data.
	 */
	protected $value = [];

	/**
	 * Set the base name of the array element.
	 * 
	 * @param string $name Tag name.
	 * @return self
	 */
	public function __construct($name)
	{
		$this->name = end(explode(':', (string) $name));
	}

	/**
	 * Reset the element’s contents on cloning.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->setValue(NULL);
	}

	/**
	 * Get the collection elements of the array element.
	 * 
	 * @return array
	 */
	public function getValue()
	{
		return $this->value;
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
	 * Convert and/or validate the data item.
	 * 
	 * @param mixed $value 
	 * @return mixed
	 * @throws Exception Value constraint violation.
	 */
	abstract protected function convert($value);

	/**
	 * Get the element’s tag name (local name).
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

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
	 * Transform the element into its XML representation.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->getName());

		foreach ($this->value as $value) {
			$node->appendChild($value->toDOM($doc));
		}

		return $node;
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

	/**
	 * Count the number of elements in the collection. 
	 * 
	 * @return integer
	 */
	public function count()
	{
		return count($this->value);
	}
}
