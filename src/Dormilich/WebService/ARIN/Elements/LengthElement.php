<?php

namespace Dormilich\WebService\ARIN\Elements;

class LengthElement extends Element
{
	protected $length = 1;

	public function __construct($name, $length)
	{
		parent::__construct($name);

		$this->length = filter_var($length, \FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1, 'default' => 1]
		]);
	}

	public function setValue($value)
	{
		if (strlen((string) $value) !== $this->length) {
			$msg = 'Value "%s" does not match the expected length of %d for the [%s] attribute.';
			throw new \Exception(sprintf($msg, $value, $this->length, $this->name));
		}

		parent::setValue($value);
	}
}
