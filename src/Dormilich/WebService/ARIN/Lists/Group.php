<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\FilterInterface;
use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class accepts any serialisable object(s) as its content.
 * The main use of this class is to provide a container for nested payloads.
 */
class Group extends ArrayElement implements FilterInterface
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
		throw new DataTypeException(sprintf($msg, $type, $this->name));
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
	 * Check if the requested index or an element with that name exists.
	 * 
	 * @param integer|string $offset Collection element index or name.
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		// indexed
		if (parent::offsetExists($offset)) {
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
		if (parent::offsetExists($offset)) {
			return parent::offsetGet($offset);
		}
		// first named
		return $this->fetch($offset);
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
			// quasi-leaf
			$payload = $ns . ucfirst($name);
			if (class_exists($payload)) {
				$elem = new $payload;
			}
			// node
			elseif ($child->hasChildren()) {
				$elem = new Group($name);
			}
			// leaf
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

        if ($uri = current($ns)) {
            return new Element(key($ns).':'.$sxe->getName(), $uri);
        }
        return new Element($sxe->getName());
    }
}
