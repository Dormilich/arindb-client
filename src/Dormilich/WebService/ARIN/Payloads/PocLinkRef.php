<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;

/**
 * This payload is a nested object within ORG and NET Payloads, explaining the 
 * POC Handle(s) associated with that object and the function it is serving.
 * 
 * The description field will be completed automatically based on the 
 * information provided in the function field, and should be left blank.
 * 
 *     Note:Admin ("AD") POCs may not be added to NETs. 
 */
class PocLinkRef extends Payload
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
