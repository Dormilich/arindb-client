<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class accepts any serialisable object(s) as its content.
 * The main use of this class is to provide a container for nested payloads.
 */
class GroupElement extends ArrayElement
{
	/**
	 * Check if any member of the collection is defined.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		$bool = array_map(function ($item) {
			return $item->isDefined();
		}, $this->value);

		return array_reduce($bool, function ($carry, $item) {
			return $carry or $item;
		}, false);
	}

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
		$msg = 'Value of type %s is not a valid object for the [%s] element.';
		$type = is_object($value) ? get_class($value) : gettype($value);
		throw new DataTypeException(sprintf($msg, $type, $this->name));
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
