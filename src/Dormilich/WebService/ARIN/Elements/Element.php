<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * An Element represents a single XML tag without nested XML tags.
 * 
 * Note: __toString() would be neat, but causes too much trouble for the 
 *       inheriting collection elements.
 */
class Element implements ElementInterface
{
	/**
	 * @var string $name The element’s tag name.
	 */
	protected $name;

	/**
	 * @var string $prefix XML namespace prefix.
	 */
	protected $prefix;

	/**
	 * @var string $namespace XML namespace URI.
	 */
	protected $namespace;

	/**
	 * @var string $value The textContent of the element.
	 */
	protected $value = '';

	/**
	 * @var array $attributes XML attibute definitions.
	 */
	protected $attributes = [];

	/**
	 * Setting up the basic XML definition. The name may be either a tag name
	 * —or if a namespace is given—a qualified name.
	 * 
	 * @param string $name Tag name.
	 * @param string $ns Namespace URI.
	 * @return self
	 */
	public function __construct($name, $ns = NULL)
	{
		if ($ns === NULL) {
			$this->name = end(explode(':', $name));
		}
		elseif (filter_var($ns, \FILTER_VALIDATE_URL)) {
			if (strpos($name, ':') === false) {
				throw new \LogicException('Namespace prefix missing.');
			}
			list($this->prefix, $this->name) = explode(':', $name);

			$this->namespace = $ns;
		}
		else {
			throw new \LogicException('Invalid namespace.');
		}
	}

	/**
	 * Reset the element’s contents on cloning.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->setValue(NULL);
		$this->attributes = [];
	}

	/**
	 * Getter for an attribute.
	 * 
	 * @param string $name XML attribute name.
	 * @return string|NULL Attribute value.
	 */
	public function __get($name)
	{
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		return NULL;
	}

	/**
	 * Setter for an attribute. If the attribute does not exist yet, it is 
	 * created.
	 * 
	 * @param string $name Attribute name.
	 * @param string $value New attribute value.
	 * @return type
	 */
	public function __set($name, $value)
	{
		$this->attributes[$name] = (string) $value;
	}

	/**
	 * Remove an attribute.
	 * 
	 * @param string $name 
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->attributes[$name]);
	}

	/**
	 * Get the text content of the element.
	 * 
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Set the text content of the element. The value may be any type that 
	 * can be stringified.
	 * 
	 * @param string $value New element text content.
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = '';

		if (NULL !== $value) {
			$this->value = $this->convert($value);
		}

		return $this;
	}

	/**
	 * Set the text content of the element. The value may be any type that 
	 * can be stringified.
	 * 
	 * @param string $value New element text content.
	 * @return self
	 */
	public function addValue($value)
	{
		$this->setValue($value);

		return $this;
	}

	/**
	 * Convert the data item into a string.
	 * 
	 * @param mixed $value 
	 * @return string
	 * @throws Exception Value not stringifiable.
	 */
	protected function convert($value)
	{
		if (is_scalar($value) or (is_object($value) and method_exists($value, '__toString'))) {
			return (string) $value;
		}
		$msg = 'Value of type %s cannot be converted to a string for the [%s] element.';
		throw new DataTypeException(sprintf($msg, gettype($value), $this->name));
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
	 * Returns TRUE if the element’s text content is not empty.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return strlen($this->value) > 0;
	}

	/**
	 * @inheritDoc
	 */
	public function toDOM(\DOMDocument $doc)
	{
		$elem = $this->createElement($doc);

		// technically, attributes would also be dependent 
		// on the namespace, but in ARIN payloads all 
		// attributes belong to the parent namespace
		foreach ($this->attributes as $name => $value) {
			$elem->setAttribute($name, $value);
		}

		return $elem;
	}

	/**
	 * Create the base XML element (without attributes).
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	protected function createElement(\DOMDocument $doc)
	{
		if (!$this->namespace) {
			return $doc->createElement($this->name, $this->value);
		}
		$name = $this->prefix . ':' . $this->name;
		return $doc->createElementNS($this->namespace, $name, $this->value);
	}
}
