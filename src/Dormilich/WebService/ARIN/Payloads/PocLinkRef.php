<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;

class PocLinkRef extends Payload implements DOMSerializable
{
	public function __construct()
	{
		$this->name = 'pocLinkRef';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('description'));
		$this->create(new Element('handle'));
		$this->create(new FixedElement('function', ['AD', 'AB', 'N', 'T']));
	}

	public function isDefined()
	{
		return $this->getAttribute('function')->isDefined();
	}

	protected function addXMLElements(\DOMDocument $doc, \DOMElement $node)
	{
		foreach ($this->attributes as $attr) {
			if ($attr->isDefined()) {
				$node->setAttribute($attr->getName(), $attr->getValue());
			}
		}

		return $node;
	}
}
