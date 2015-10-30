<?php

namespace Dormilich\WebService\ARIN\Elements;

class FixedElement extends Element
{
	protected $allowed = [];

	public function __construct($name, array $allowed)
	{
		parent::__construct($name);

		$this->allowed = $allowed;
	}

	public function setValue($value)
	{
		if (!in_array($value, $this->allowed, true)) {
			$msg = 'Value "%s" is not allowed for the [%s] attribute.';
			throw new \Exception(sprintf($msg, $value, $this->name));
		}

		parent::setValue($value);
	}
}
