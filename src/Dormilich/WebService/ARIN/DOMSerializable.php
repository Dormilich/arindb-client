<?php

namespace Dormilich\WebService\ARIN;

interface DOMSerializable
{
	public function getName();

	public function isDefined();

	public function toDOM(\DOMDocument $doc);
}
