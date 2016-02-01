<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\Exceptions\DataTypeException;
use Dormilich\WebService\ARIN\Exceptions\ParserException;

/**
 * Defines a multi-line field, such as public comments or address. 
 * ---
 * Although ARIN defines it as a separate Payload, the Payload’s root element 
 * name (multiline) is replaced by the respective element names (comment, 
 * streetAddress). 
 */
class MultiLine extends Group
{
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
	 * Convert the data item into a string.
	 * 
	 * @param mixed $value 
	 * @return string
	 * @throws DataTypeException Value not stringifiable.
	 */
	protected function convert($value)
	{
		if (is_object($value) and method_exists($value, '__toString')) {
			$value = (string) $value;
		}

		if (!is_scalar($value)) {
			$msg = 'Value of type %s cannot be converted to a string for the [%s] element.';
			throw new DataTypeException(sprintf($msg, gettype($value), $this->getName()));
		}

		return (string) $value;
	}

	/**
	 * Returns TRUE if the element’s data collection is not empty.
	 * 
	 * @return boolean
	 */
	public function isValid()
	{
		return count($this->value) > 0;
	}

	/**
	 * Check if there are members in the collection. 
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return count($this->value) > 0;
	}

	/**
	 * Createa multi-line payload using the defined tag name.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->getName());

		foreach ($this->value as $index => $value) {
			$child = $doc->createElement('line');
			$child->textContent = $value;
			$child->setAttribute('number', ++$index);
			$node->appendChild($child);
		}

		return $node;
	}

	/**
	 * @inheritDoc
	 */
	public function parse(\SimpleXMLElement $sxe)
	{
		if ($this->getName() !== $sxe->getName()) {
			throw new ParserException('Tag name mismatch on reading XML.');
		}
		
		foreach ($sxe->children() as $line) {
			$this->addValue($line);
		}
	}

	/**
	 * Check if the requested index exists.
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
}
