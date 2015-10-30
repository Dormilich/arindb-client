<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;

abstract class Payload implements \ArrayAccess
{
	const XMLNS = 'http://www.arin.net/regrws/core/v1';

	protected $name;

	protected $elements = [];

	abstract protected function init();

	protected function create(DOMSerializable $elem, $alias = NULL)
	{
		if (!$alias) {
			$alias = $elem->getName();
		}

		if (isset($this->elements[$alias])) {
			throw new \LogicException('Duplicate attribute alias '.$alias);
		}

		$this->elements[$alias] = $elem;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getElement($name)
	{
		if (isset($this->elements[$name])) {
			return $this->elements[$name];
		}

		$attr = array_filter($this->elements, function ($item) use ($name) {
			return $item->getName() === $name;
		});

		if (count($attr) === 0) {
			throw new \Exception('Element '.$name.' not found.');
		}

		return reset($attr);
	}

	protected function addXMLElements(\DOMDocument $doc, \DOMElement $node)
	{
		foreach ($this->elements as $attr) {
			if ($attr->isDefined()) {
				$node->appendChild($attr->toDOM($doc));
			}
		}

		return $node;
	}

	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->name);

		return $this->addXMLElements($doc, $node);
	}

	public function toXML()
	{
		$doc = \DOMImplementation::createDocument(self::XMLNS, $this->name);

		$this->addXMLElements($doc, $doc->documentElement);

		return $doc;
	}

	public function offsetExists($offset)
	{
		try {
			$this->getElement($offset);
			return true;
		}
		catch (\Exception $e) {
			return false;
		}
	}

	public function offsetGet($offset)
	{
		return $this->getElement($offset);
	}

	public function offsetSet($offset, $value)
	{
		$this->getElement($offset)->addValue($value);
	}

	public function offsetUnset($offset)
	{
		$this->getElement($offset)->setValue(NULL);
	}

	public function set($name, $value)
	{
		$this->offsetSet($name, $value);

		return $this;
	}
}
