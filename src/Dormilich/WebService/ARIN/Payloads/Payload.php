<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\ElementInterface;
use Dormilich\WebService\ARIN\Exceptions\ARINException;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;
use Dormilich\WebService\ARIN\Exceptions\NotFoundException;

abstract class Payload implements DOMSerializable, \ArrayAccess, \Iterator
{
	/**
	 * @var string REG-RWS XML namespace.
	 */
	const XMLNS = 'http://www.arin.net/regrws/core/v1';

	/**
	 * @var string Name of the Payload’s XML base element.
	 */
	protected $name;

	/**
	 * @var array(DOMSerializable) Child elements of the Payload.
	 */
	protected $elements = [];

	/**
	 * Set up the definition of the Payload’s child elements.
	 * 
	 * @return void
	 */
	abstract protected function init();

	/**
	 * Add a serialisable Element to the element list. The alias can be used 
	 * to shorten overly long XML element names or avoid access issues if 
	 * there are multiple XML elements with the same tag name.
	 * 
	 * @param ElementInterface $elem 
	 * @param $alias An alias for the element's name should the element have 
	 *          an inconvenient or duplicate name.
	 * @return void
	 * @throws LogicException name or alias already exists.
	 */
	protected function create(ElementInterface $elem, $alias = NULL)
	{
		if (!$alias) {
			$alias = $elem->getName();
		}

		if (array_key_exists($alias, $this->elements)) {
			throw new \LogicException('Duplicate attribute alias '.$alias);
		}

		$this->elements[$alias] = $elem;
	}

	/**
	 * Reset the payload’s elements on cloning.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->elements = array_map(function ($elem) {
			return clone $elem;
		}, $this->elements);
	}

	/**
	 * By default, Payloads are always subject to serialisation.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return true;
	}

	/**
	 * Get the name of the Payload’s base XML element.
	 * 
	 * @return string Base XML element’s tag name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get a child element by name or alias. First the name is looked up in 
	 * the element array’s keys. If it is not found, get all elements of that 
	 * name and return the first one. There is no recursion.
	 * 
	 * Note: the return type is the same as the input type of create().
	 * Note: the exception should be more specific.
	 * 
	 * @param string $name Element name or alias.
	 * @return DOMSerializable Element.
	 * @throws Exception Element not found.
	 */
	public function get($name)
	{
		if (isset($this->elements[$name])) {
			return $this->elements[$name];
		}

		$elem = array_filter($this->elements, function ($item) use ($name) {
			return $item->getName() === $name;
		});

		if (count($elem) === 0) {
			throw new NotFoundException('Element '.$name.' not found.');
		}

		return reset($elem);
	}

	/**
	 * Set a named or aliased element’s value. If an Payload object is passed 
	 * it replaces the previous object if it is from the same class. This is 
	 * intended for easy assignment of sub-payloads.
	 * 
	 * @param string $name Element name or alias.
	 * @param mixed $value Element value.
	 * @return self
	 */
	public function set($name, $value)
	{
		$elem = $this->get($name);

		if (($value instanceof Payload) and ($value instanceof $elem)) {
			$key = array_search($elem, $this->elements, true);
			$this->elements[$key] = $value;
		}
		else {
			$elem->setValue($value);
		}

		return $this;
	}

	/**
	 * Chainable method to add a value to an element.
	 * 
	 * @param string $name Element name or alias.
	 * @param mixed $value Element value.
	 * @return self
	 */
	public function add($name, $value)
	{
		$this->get($name)->addValue($value);

		return $this;
	}

	/**
	 * Transform the child element and append them to the given node.
	 * 
	 * @param DOMDocument $doc 
	 * @param DOMElement $node Node to append elements to and return.
	 * @return DOMElement The node with the appended elements.
	 */
	protected function addXMLElements(\DOMDocument $doc, \DOMElement $node)
	{
		foreach ($this->elements as $elem) {
			if ($elem->isDefined()) {
				$node->appendChild($elem->toDOM($doc));
			}
		}

		return $node;
	}

	/**
	 * Create the Payload’s base element and append child elements.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement The Payload’s base DOM element.
	 */
	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->name);

		return $this->addXMLElements($doc, $node);
	}

	/**
	 * Create a new XML document and serialise the payload’s contents.
	 * 
	 * @return DOMDocument The Payload’s XML document.
	 */
	public function toXML()
	{
		$doc = \DOMImplementation::createDocument(self::XMLNS, $this->name);

		$this->addXMLElements($doc, $doc->documentElement);

		return $doc;
	}

	/**
	 * Check if a named or aliased element exists.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param string $offset Element name or alias.
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		try {
			$this->get($offset);
			return true;
		}
		catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Get a named or aliased element via array access.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param string $offset Element name or alias.
	 * @return ElementInterface
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Set the value of an element via array access.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param string $offset Element name or alias.
	 * @param mixed $value 
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Unset the content of a named or aliased element.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param string $offset Element name or alias.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		try {
			$elem = $this->get($offset);
			$key = array_search($elem, $this->elements, true);

			$this->elements[$key] = clone $elem;
		}
		catch (ARINException $e) {
			# unsetting a non-existing element should do no harm
		}
	}

	/**
	 * Reset the elements array pointer to the beginning.
	 * 
	 * @see http://php.net/Iterator
	 * 
	 * @return void
	 */
	public function rewind()
	{
		reset($this->elements);
	}
	
	/**
	 * Get the current element.
	 * 
	 * @see http://php.net/Iterator
	 * 
	 * @return ElementInterface
	 */
	public function current()
	{
		return current($this->elements);
	}
	
	/**
	 * Get the current element’s name (not its alias).
	 * 
	 * @see http://php.net/Iterator
	 * 
	 * @return string
	 */
	public function key()
	{
		return current($this->elements)->getName();
	}
	
	/**
	 * Forward the elements array pointer.
	 * 
	 * @see http://php.net/Iterator
	 * 
	 * @return void
	 */
	public function next()
	{
		next($this->elements);
	}
	
	/**
	 * Returns FALSE if the elements array pointer is at the last item.
	 * 
	 * @see http://php.net/Iterator
	 * 
	 * @return boolean
	 */
	public function valid()
	{
		// elements are always objects
		return false !== current($this->elements);
	}
}
