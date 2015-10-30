<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\DOMSerializable;

class GroupElement extends ArrayElement
{
	protected function convert($value)
	{
		if (! $value instanceof DOMSerializable) {
			throw new \Exception('Value is not a valid object.');
		}
		return $value;
	}

	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->name);

		foreach ($this->value as $value) {
			$node->appendChild($value->toDOM($doc));
		}

		return $node;
	}
}
