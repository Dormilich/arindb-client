<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\FilterInterface;
use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\ElementInterface;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;
use Dormilich\WebService\ARIN\Exceptions\ParserException;

/**
 * This class accepts any serialisable object(s) as its content.
 * The main use of this class is to provide a container for nested payloads.
 */
class Group implements ElementInterface, FilterInterface, XMLHandler, \ArrayAccess, \Countable
{
	/**
	 * @var string $name The element’s tag name.
	 */
	protected $name;

	/**
	 * @var array $value Collection of the nested data.
	 */
	protected $value = [];

	/**
	 * Set the base name of the array element.
	 * 
	 * @param string $name Tag name.
	 * @return self
	 */
	public function __construct($name)
	{
		$this->name = end(explode(':', (string) $name));
	}

	/**
	 * Reset the element’s contents on cloning.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->setValue(NULL);
	}

	/**
	 * Get the collection elements of the array element.
	 * 
	 * @return array
	 */
	public function getValue()
	{
		$defined_only = func_get_arg(0);

		return array_map(function ($e) use ($defined_only) {
			return $e->getValue($defined_only);
		}, $this->value);
	}

	/**
	 * Discard the existing data and add the new content. A collection can 
	 * also be set using an array of coresponding data.
	 * 
	 * @param array|mixed $value Value item(s) to set.
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = [];

		if (NULL === $value) {
			return $this;
		}

		if (is_array($value)) {
			foreach ($value as $item) {
				$this->addValue($item);
			}
		}
		else {
			$this->addValue($value);
		}

		return $this;
	}

	/**
	 * Add a single data item to the collection.
	 * 
	 * @param mixed $value 
	 * @return self
	 */
	public function addValue($value)
	{
		$this->value[] = $this->convert($value);

		return $this;
	}

	/**
	 * Check if the value is a serialisable element.
	 * 
	 * @param object $value 
	 * @return XMLHandler
	 * @throws DataTypeException Value is not serialisable.
	 */
	protected function convert($value)
	{
		if ($value instanceof XMLHandler) {
			return $value;
		}
		$msg = 'Value of type %s is not a valid object for the [%s] element.';
		$type = is_object($value) ? get_class($value) : gettype($value);
		throw new DataTypeException(sprintf($msg, $type, $this->getName()));
	}

	/**
	 * Check if the value is supported.
	 * 
	 * @param XMLHandler $value 
	 * @return boolean
	 */
	public function supports(XMLHandler $value)
	{
		return true;
	}

	/**
	 * Get the element’s tag name (local name).
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Check if any member of the collection is valid.
	 * 
	 * @return boolean
	 */
	public function isValid()
	{
		$bool = array_map(function ($item) {
			return $item->isValid();
		}, $this->value);

		return array_reduce($bool, function ($carry, $item) {
			return $carry or $item;
		}, false);
	}

	/**
	 * Check if there are members in the collection. 
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return count($this->value) > 0;
	}

	/**
	 * Transform the element into its XML representation.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	public function toDOM(\DOMDocument $doc)
	{
		$node = $doc->createElement($this->getName());

		foreach ($this->value as $value) {
			$node->appendChild($value->toDOM($doc));
		}

		return $node;
	}

	/**
	 * Get all elements whose tag name matches the given value.
	 * 
	 * @param mixed $name Tag name.
	 * @return array List of matching elements.
	 */
	public function filter($name)
	{
		return array_filter($this->value, function ($item) use ($name) {
			return $item->getName() === $name;
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
		return reset($this->filter($name)) ?: NULL;
	}

	/**
	 * @inheritDoc
	 */
	public function parse(\SimpleXMLElement $sxe)
	{
		if ($this->getName() !== $sxe->getName()) {
			throw new ParserException('Tag name mismatch on reading XML.');
		}
		$ns = substr_replace(__NAMESPACE__, '\\Payloads\\', strrpos(__NAMESPACE__, '\\'));

		foreach ($sxe->children() as $name => $child) {
			$payload = $ns . ucfirst($name);
			if (class_exists($payload)) {
				$elem = new $payload;
				// unfortunately there are cases where a simple element has the 
				// same name as a payload (e.g. Error Payload)
				if (!$this->supports($elem)) {
					$elem = $this->createElement($child);
				}
			}
			elseif (count($child)) {
				$elem = new Group($name);
			}
			else {
				$elem = $this->createElement($child);
			}
			$elem->parse($child);
			$this->addValue($elem);
		}
	}

    /**
     * Read the namespace info from the XML object and configure the Element 
     * object accordingly.
     * 
     * @param SimpleXMLElement $sxe 
     * @return Element
     */
    protected function createElement(\SimpleXMLElement $sxe)
    {
        $ns = $sxe->getNamespaces();

        if (key($ns)) {
            return new Element(key($ns).':'.$sxe->getName(), current($ns));
        }
        return new Element($sxe->getName());
    }

	/**
	 * Check if the requested index or an element with that name exists.
	 * 
	 * @param integer|string $offset Collection element index or name.
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		// indexed
		if (isset($this->value[$offset])) {
			return true;
		}
		// named
		return count($this->filter($offset)) > 0;
	}

	/**
	 * Get the requested element from the collection. Returns NULL if index 
	 * does not exist.
	 * 
	 * @param integer|string $offset Collection element index or name.
	 * @return mixed Returns NULL if index does not exist.
	 */
	public function offsetGet($offset)
	{
		// indexed
		if (isset($this->value[$offset])) {
			return $this->value[$offset];
		}
		// first named
		return $this->fetch($offset);
	}

	/**
	 * Set an element at the requested index. If the index is not found in the 
	 * collection, the value is appended instead.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param integer $offset Collection element index.
	 * @param mixed $value Replacement value.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		if ($this->offsetExists($offset)) {
			$this->value[$offset] = $this->convert($value);
		}
		else {
			$this->addValue($value);
		}
	}

	/**
	 * Remove the element at the requested position. The collection will be 
	 * re-indexed after the removal.
	 * 
	 * @see http://php.net/ArrayAccess
	 * 
	 * @param integer $offset Collection element index.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		if ($this->offsetExists($offset)) {
			array_splice($this->value, $offset, 1);
		}
	}

	/**
	 * Count the number of elements in the collection. 
	 * 
	 * @return integer
	 */
	public function count()
	{
		return count($this->value);
	}
}
