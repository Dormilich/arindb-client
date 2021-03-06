<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DefinedPayloadFilter;
use Dormilich\WebService\ARIN\ElementInterface;
use Dormilich\WebService\ARIN\FilterInterface;
use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Lists\Group;
use Dormilich\WebService\ARIN\Exceptions\ARINException;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;
use Dormilich\WebService\ARIN\Exceptions\NotFoundException;
use Dormilich\WebService\ARIN\Exceptions\ParserException;

abstract class Payload implements XMLHandler, \JsonSerializable, \ArrayAccess, \Iterator
{
	/**
	 * @var string REG-RWS XML namespace.
	 */
	protected $xmlns = 'http://www.arin.net/regrws/core/v1';

	/**
	 * @var string Name of the Payload’s XML base element.
	 */
	protected $name;

	/**
	 * @var array(XMLHandler) Child elements of the Payload.
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
	 * @param XMLHandler $elem A serialisable element.
	 * @param $alias An alias for the element's name should the element have 
	 *          an inconvenient or duplicate name.
	 * @return void
	 * @throws LogicException name or alias already exists.
	 */
	protected function create(XMLHandler $elem, $alias = NULL)
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
	 * Returns TRUE if all elements are defined.
	 * 
	 * @return boolean
	 */
	public function isValid()
	{
		return array_reduce($this->elements, function ($carry, $item) {
			return $carry and $item->isValid();
		}, true);
	}

	/**
	 * Check if an element is empty. This is used to select and display only 
	 * elements with a defined value.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return array_reduce($this->elements, function ($carry, $item) {
			return $carry or $item->isDefined();
		}, false);
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
	 * Convenience method to check an elements name.
	 * 
	 * @param string $name Tag name.
	 * @return boolean
	 */
	public function hasName($name)
	{
		return $this->getName() === $name;
	}

	/**
	 * Fallback if a payload’s value is accessed as if it were an element. 
	 * Can also be used to convert the payload into an array.
	 * 
	 * @param bool $defined_only Flag to include only defined elements.
	 * @return array
	 */
	public function getValue($defined_only = false)
	{
		if ($defined_only) {
			$iterator = new \CallbackFilterIterator($this, function ($current) {
				return $current->isDefined();
			});
		}
		else {
			$iterator = $this;
		}

		return array_map(function ($e) use ($defined_only) {
			return $e->getValue($defined_only);
		}, iterator_to_array($iterator));
	}

	/**
	 * Polyfill for the third parameter to array_filter() (as of PHP 5.6).
	 * 
	 * @param array $array The array to filter.
	 * @param callable $callback The callback function
	 * @return array The filtered array.
	 */
	private function filterArray(array $array, callable $callback)
	{
		if (defined('ARRAY_FILTER_USE_BOTH')) {
			return array_filter($array, $callback, \ARRAY_FILTER_USE_BOTH);
		}
		$result = [];
		foreach ($array as $key => $value) {
			if (call_user_func($callback, $value, $key)) {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * Get all elements whose tag name matches the given value(s).
	 * 
	 * @param mixed $name Tag name.
	 * @return array List of matching elements.
	 */
	public function filter($name)
	{
		$list = func_get_args();
		return $this->filterArray($this->elements, function ($item, $alias) use ($list) {
			return in_array($alias, $list, true) or in_array($item->getName(), $list, true);
		});
	}

	/**
	 * Get the first element whose tag name matches the given value.
	 * 
	 * @param mixed $name Tag name.
	 * @return object|NULL First matching element or NULL if no matching 
	 *          element was found.
	 */
	public function fetch($name)
	{
		$elements = $this->filter($name);
		return reset($elements) ?: NULL;
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
	 * @return XMLHandler Element.
	 * @throws NotFoundException Element not found.
	 */
	public function get($name)
	{
		if (isset($this->elements[$name])) {
			return $this->elements[$name];
		}

		$elem = $this->fetch($name);

		if (!$elem) {
			throw new NotFoundException('Element '.$name.' not found.');
		}

		return $elem;
	}

	/**
	 * Set a named or aliased element’s value. If an Payload object is passed 
	 * it replaces the previous object if it is from the same class. This is 
	 * intended for easy assignment of sub-payloads. If the value is not a 
	 * matching payload, an notice is emitted.
	 * 
	 * @param string $name Element name or alias.
	 * @param mixed $value Element value.
	 * @return self
	 */
	public function set($name, $value)
	{
		// elements are either Payloads or Elements
		$elem = $this->get($name);

		// swap payloads
		if (($elem instanceof Payload) and ($value instanceof $elem)) {
			$key = array_search($elem, $this->elements, true);
			$this->elements[$key] = $value;
		}
		// reset payload
		elseif (($elem instanceof Payload) and ($value === NULL)) {
			$key = array_search($elem, $this->elements, true);
			$class = get_class($elem);
			$this->elements[$key] = new $class;
		}
		// if target is an element/group
		elseif ($elem instanceof ElementInterface) {
			$elem->setValue($value);
		}
		else {
			$msg = 'Value of type [%s] cannot overwrite a <%s> Payload.';
			$type = is_object($value) ? get_class($value) : gettype($value);
			trigger_error(sprintf($msg, $type, $elem->getName()), \E_USER_NOTICE);
		}

		return $this;
	}

	/**
	 * Chainable method to add a value to an element. Emits a notice if the 
	 * target is not an element.
	 * 
	 * @param string $name Element name or alias.
	 * @param mixed $value Element value.
	 * @return self
	 */
	public function add($name, $value)
	{
		$elem = $this->get($name);

		if ($elem instanceof ElementInterface) {
			$elem->addValue($value);
		}
		else {
			$msg = 'Value of type [%s] cannot overwrite a <%s> Payload.';
			$type = is_object($value) ? get_class($value) : gettype($value);
			trigger_error(sprintf($msg, $type, $elem->getName()), \E_USER_NOTICE);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function parse(\SimpleXMLElement $sxe)
	{
		if ($this->getName() !== $sxe->getName()) {
			throw new ParserException('Tag name mismatch on reading XML.');
		}

		$ns = $sxe->getDocNamespaces();
		// a bit of a hack to make it work with and without namespaces
		if (count($ns) === 0) {
			$ns[] = NULL;
		}

		foreach ($ns as $prefix => $namespace) {
			foreach ($sxe->children($namespace) as $name => $child) {
				$this->getEmptyElement($name)->parse($child);
			}
		}

		return $this;
	}

	/**
	 * For the rare case when there are multiple elements of the same name in 
	 * the XML (e.g. comment), fetch the next empty payload element of that name. 
	 * 
	 * Note: there is currently no supporting payload definition for that.
	 * 
	 * @param string $name 
	 * @return XMLHandler
	 * @throws ParserException Invalid Payload setup.
	 */
	private function getEmptyElement($name)
	{
		$elem = $this->get($name);

		if (!$elem->isValid()) {
			return $elem;
		}

		$empty = array_filter($this->filter($name), function ($item) {
			return !$item->isValid();
		});

		if (count($empty) === 0) {
			throw new ParserException('Payload setup and XML structure mismatch.');
		}

		return reset($empty);
	}

	/**
	 * Transform the child elements and append them to the given node.
	 * 
	 * @param DOMDocument $doc 
	 * @param DOMElement $node Node to append elements to and return.
	 * @return DOMElement The node with the appended elements.
	 */
	protected function addXMLElements(\DOMDocument $doc, \DOMElement $node)
	{
		foreach ($this->elements as $elem) {
			if ($elem->isValid()) {
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
		$node = $doc->createElement($this->getName());

		return $this->addXMLElements($doc, $node);
	}

	/**
	 * Create a new XML document and serialise the payload’s contents.
	 * 
	 * @param string|NULL $encoding The document (charset) encoding.
	 * @param boolean $validate Check the payload’s validity before serialisation.
	 * @return DOMDocument The Payload’s XML document.
	 * @throws ParserException Invalid document.
	 */
	public function toXML($encoding = 'UTF-8', $validate = XMLHandler::VALIDATE)
	{
		if (XMLHandler::NOVALIDATE !== $validate and !$this->isValid()) {
			throw new ParserException(ucfirst($this->getName()) . ' Payload is not valid for submission.');
		}
		$doc = new \DOMDocument('1.0', $encoding);
		$root = $doc->createElementNS($this->xmlns, $this->getName());
		$doc->appendChild($root);

		$this->addXMLElements($doc, $doc->documentElement);

		return $doc;
	}

	/**
	 * Parse an XML string into the respective payload object. If no suitable 
	 * payload class is found, put it into a group. 
	 * 
	 * @param string $xmlString 
	 * @return Payload|Group
	 * @throws ErrorException XML reading error.
	 */
	public static function loadXML($xmlString)
	{
		set_error_handler(function ($code, $msg, $file, $line) {
			restore_error_handler();
			throw new \ErrorException($msg, 0, $code, $file, $line);
		});
		$xml = simplexml_load_string($xmlString);
		$class = __NAMESPACE__ . '\\' . ucfirst($xml->getName());
		restore_error_handler();

		if (class_exists($class)) {
			$payload = new $class;
		}
		else {
			$payload = new Group($xml->getName());
		}
		return $payload->parse($xml);
	}

	/**
	 * @see http://php.net/JsonSerializable
	 */
	public function jsonSerialize()
	{
		return $this->getValue(true);
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
	 * Accessing nested elements via array access is worth more than getting a 
	 * child element’s value directly.
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
			$this->set($offset, NULL);
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
