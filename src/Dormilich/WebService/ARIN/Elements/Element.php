<?php

namespace Dormilich\WebService\ARIN\Elements;

class Element implements ElementInterface
{
	protected $name;

	protected $prefix;

	protected $namespace;

	protected $value = '';

	protected $attributes = [];

	public function __construct($name, $ns = NULL)
	{
		if ($ns === NULL) {
			$this->name = $name;
		}
		elseif (filter_var($ns, \FILTER_VALIDATE_URL)) {
			if (strpos($name, ':') === false) {
				throw new \LogicException('Namespace prefix missing.');
			}
			$parts = explode(':', $name);
			$this->prefix = $parts[0];
			$this->name = $parts[1];

			$this->namespace = $ns;
		}
		else {
			throw new \LogicException('Invalid namespace.');
		}
	}

	public function __get($name)
	{
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		return NULL;
	}

	public function __set($name, $value)
	{
		$this->attributes[$name] = (string) $value;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = (string) $value;

		return $this;
	}

	public function addValue($value)
	{
		return $this->setValue($value);
	}

	public function getName()
	{
		return $this->name;
	}

	public function isDefined()
	{
		return strlen($this->value) > 0;
	}

	public function toDOM(\DOMDocument $doc)
	{
		$elem = $this->createElement($doc);

		// technically, attributes would also be dependent 
		// on the namespace, but in ARIN payloads all 
		// attributes belong to the default namespace
		foreach ($this->attributes as $name => $value) {
			$elem->setAttribute($name, $value);
		}

		return $elem;
	}

	protected function createElement(\DOMDocument $doc)
	{
		if (!$this->namespace) {
			return $doc->createElement($this->name, $this->value);
		}
		$name = $this->prefix . ':' . $this->name;
		return $doc->createElementNS($this->namespace, $name, $this->value);
	}
}
