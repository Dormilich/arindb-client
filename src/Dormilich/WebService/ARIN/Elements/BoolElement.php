<?php

namespace Dormilich\WebService\ARIN\Elements;

class BoolElement extends Element
{
	public function setValue($value)
	{
		if (filter_var($value, \FILTER_VALIDATE_BOOLEAN)) {
			$this->value = 'true';
		}
		else {
			$this->value = 'false';
		}
	}
}
