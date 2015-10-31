<?php

namespace Dormilich\WebService\ARIN\Elements;

/**
 * Defines a multi-line field, such as public comments or address. 
 * ---
 * Although ARIN defines it as a separate Payload, the Payload’s root element 
 * name (multiline) is replaced by the respective element names (comment, 
 * streetAddress). 
 */
class MultilineElement extends ArrayElement
{
	/**
	 * Createa multi-line payload using the defined tag name.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->name);

		foreach ($this->value as $index => $value) {
			$child = $doc->createElement('line', $value);
			$child->setAttribute('number', ++$index);
			$node->appendChild($child);
		}

		return $node;
	}
}
