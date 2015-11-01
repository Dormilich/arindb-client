<?php

namespace Test;

/**
 * A stringifiable class.
 */
class Stringer
{
	protected $value;
	
	public function __construct($value)
	{
		$this->value = (string) $value;
	}

	public function __toString()
	{
		return $this->value;
	}
}
