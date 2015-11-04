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
class MultiLine extends ArrayElement
{
	/**
	 * Convert the data item into a string.
	 * 
	 * @param mixed $value 
	 * @return string
	 * @throws Exception Value not stringifiable.
	 */
	protected function convert($value)
	{
		if (is_scalar($value) or (is_object($value) and method_exists($value, '__toString'))) {
			return (string) $value;
		}
		$msg = 'Value of type %s cannot be converted to a string for the [%s] element.';
		throw new DataTypeException(sprintf($msg, gettype($value), $this->name));
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
			$child = $doc->createElement('line', $value);
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
}
