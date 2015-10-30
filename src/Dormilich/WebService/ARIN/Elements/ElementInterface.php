<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\DOMSerializable;

interface ElementInterface extends DOMSerializable
{
	public function getValue();

	public function setValue($value);

	public function addValue($value);
}
