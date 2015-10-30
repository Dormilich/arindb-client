<?php

namespace Dormilich\WebService\ARIN\Elements;

abstract class ArrayElement extends Element
{
	protected $value = [];

	public function isDefined()
	{
		return count($this->value) > 0;
	}

	public function setValue($value)
	{
		$this->value = [];

		foreach ((array) $value as $item) {
			$this->addValue($item);
		}

		return $this;
	}

	public function addValue($value)
	{
		$this->value[] = $this->convert($value);

		return $this;
	}

	abstract protected function convert($value);
}
