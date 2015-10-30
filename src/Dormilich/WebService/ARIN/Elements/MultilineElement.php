<?php

namespace Dormilich\WebService\ARIN\Elements;

class MultilineElement extends ArrayElement
{
	protected $value = [];

	protected function convert($value)
	{
		return (string) $value;
	}

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
