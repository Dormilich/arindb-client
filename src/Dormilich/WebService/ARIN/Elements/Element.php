<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\ElementInterface;
use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\Exceptions\ConstraintException;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;
use Dormilich\WebService\ARIN\Exceptions\ParserException;

/**
 * An Element represents a single XML tag without nested XML tags.
 * 
 * Note: __toString() would be neat, but causes too much trouble for the 
 *       inheriting collection elements.
 */
class Element implements ElementInterface, XMLHandler
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
	protected $value;

	/**
	 * @var array $attributes XML attibute definitions.
	 */
	protected $attributes = [];

	/**
	 * @var callable $callback Validation callback.
	 */
	private $callback;

	/**
	 * Create an Element instance with its value set. This is useful for setting 
	 * the contents of a NamedGroup.
	 * 
	 * @param string $name Element name.
	 * @param string $value Element value.
	 * @return Element
	 */
	public static function createWith($name, $value)
	{
		$element = new Element($name);
		$element->setValue($value);

		return $element;
	}

	/**
	 * Setting up the basic XML definition. The name may be either a tag name
	 * —or if a namespace is given—a qualified name.
	 * 
	 * @param string $name Tag name.
	 * @param string $ns Namespace URI.
	 * @param callable $callback A validator function.
	 * @return self
	 * @throws LogicException Invalid namespace URI.
	 * @throws LogicException Namespace prefix missing.
	 */
	public function __construct($name, $ns = NULL, callable $callback = NULL)
	{
		$this->setNamespace((string) $name, $ns);

		$this->callback = $callback;

		if ($ns and !$this->namespace) {
			throw new \LogicException('Invalid namespace.');
		}
	}

	/**
	 * Set namespace and prefix.
	 * 
	 * @param string $tag Prefixed tag name.
	 * @param string $namespace Namespace URI.
	 * @return void
	 * @throws LogicException Namespace prefix missing.
	 */
	protected function setNamespace($tag, $namespace)
	{
        if (filter_var($namespace, \FILTER_VALIDATE_URL)) {
			if (strpos($tag, ':') === false) {
				throw new \LogicException('Namespace prefix missing.');
			}
			list($this->prefix, $this->name) = explode(':', $tag);

			$this->namespace = $namespace;
        }
        else {
        	$names = explode(':', $tag);
            $this->name = end($names);
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
	 * Make the element’s content accessible in string context.
	 * 
	 * @return string The element’s value.
	 */
	public function __toString()
	{
		return (string) $this->value;
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
		$this->value = NULL;

		if (NULL !== $value) {
			$this->value = trim($this->convert($value));
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
	 * @throws DataTypeException Value not stringifiable.
	 */
	protected function convert($value)
	{
		if (is_object($value) and method_exists($value, '__toString')) {
			$value = (string) $value;
		}

		if (!is_scalar($value)) {
			$msg = 'Value of type %s cannot be converted to a string for the [%s] element.';
			throw new DataTypeException(sprintf($msg, gettype($value), $this->getName()));
		}

		return (string) $this->validate($value);
	}

	/**
	 * Validate the input value against a validation function.
	 * 
	 * @param mixed $value Input value.
	 * @return mixed Validated input value.
	 * @throws ConstraintException Validation failure.
	 */
	protected function validate($value)
	{
		if (!$this->callback) {
			return $value;
		}

		if (!call_user_func($this->callback, $value)) {
			$msg = 'Value [%s] is not allowed for the [%s] element.';
			$type = is_scalar($value) ? $value : gettype($value);
			throw new ConstraintException(sprintf($msg, $type, $this->getName()));
		}

		return $value;
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
	 * Returns TRUE if the element’s text content is not empty.
	 * 
	 * @return boolean
	 */
	public function isValid()
	{
		#return $this->value !== NULL or count($this->attributes) > 0;
		return strlen($this->value) > 0;
	}

	/**
	 * Returns TRUE if the element’s value is set.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		return $this->value !== NULL;
	}

	/**
	 * @inheritDoc
	 */
	public function parse(\SimpleXMLElement $sxe)
	{
		if ($this->getName() !== $sxe->getName()) {
			throw new ParserException('Tag name mismatch on reading XML.');
		}
		// set value
		$this->setValue($sxe);
		// set attributes
		foreach ($sxe->attributes() as $name => $value) {
			$this->__set($name, $value);
		}
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
		if ($this->prefix) {
			$name = $this->prefix . ':' . $this->name;
			$node = $doc->createElementNS($this->namespace, $name);
		}
		else {
			$node = $doc->createElement($this->name);
		}

		$node->textContent = $this->value;

		return $node;
	}
}
