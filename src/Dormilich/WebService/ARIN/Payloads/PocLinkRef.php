<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Selection;
use Dormilich\WebService\ARIN\Exceptions\ParserException;

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
		$this->create(new Selection('function', ['AD', 'AB', 'N', 'T']));
	}

	public function isValid()
	{
		return $this->get('function')->isValid();
	}

	/**
	 * Check if an pocLinkRef is properly set. Since a <pocLinkRef> only 
	 * contains XML attributes the standard check would give false positives.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return $this->get('handle')->isValid();
	}

	/**
	 * Transform the child element and append them as attributes to the given node.
	 * 
	 * @param DOMDocument $doc 
	 * @param DOMElement $node Node to append elements to and return.
	 * @return DOMElement The node with the appended elements.
	 */
	protected function addXMLElements(\DOMDocument $doc, \DOMElement $node)
	{
		foreach ($this as $name => $elem) {
			if ($elem->isValid()) {
				$node->setAttribute($name, $elem->getValue());
			}
		}

		return $node;
	}

	/**
	 * Read the data from the <pocLinkRef>’s attributes, not its 
	 * (non-existing) child elements.
	 * 
	 * @param SimpleXMLElement $sxe 
	 * @return self
	 */
	public function parse(\SimpleXMLElement $sxe)
	{
		if ($this->getName() !== $sxe->getName()) {
			throw new ParserException('Tag name mismatch on reading XML.');
		}

		foreach ($sxe->attributes() as $name => $value) {
			$this->set($name, $value);
		}

		return $this;
	}
}
