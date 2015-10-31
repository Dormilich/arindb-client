<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\DOMSerializable;

/**
 * This class accepts any serialisable object(s) as its content.
 * The main use of this class is to provide a container for nested payloads.
 */
class GroupElement extends ArrayElement
{
	/**
	 * Check if the value is a serialisable element.
	 * 
	 * @param mixed $value 
	 * @return DOMSerializable
	 * @throws Exception Value is not serialisable.
	 */
	protected function convert($value)
	{
		if ($value instanceof DOMSerializable) {
			return $value;
		}
		$msg = 'Value is not a valid object for the [%s] element.';
		throw new \Exception(sprintf($msg, $this->name));
	}

	/**
	 * Transform the element into its XML representation.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->name);

		foreach ($this->value as $value) {
			$node->appendChild($value->toDOM($doc));
		}

		return $node;
	}
}
