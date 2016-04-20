<?php

namespace Dormilich\WebService\ARIN\Elements;

class Generated extends Element
{
	/**
	 * Reset the element’s contents on cloning.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		parent::setValue(NULL);
		$this->attributes = [];
	}

	/**
	 * Set the text content of the element. Once the value is set, it cannot 
	 * be modified and will issue a PHP warning if attempted. The value may be 
	 * any type that can be stringified.
	 * 
	 * @param string $value New element text content.
	 * @return self
	 */
	public function setValue($value)
	{
		if ($this->isValid()) {
			$msg = 'The [%s] field must not be modified once it is set.';
			trigger_error(sprintf($msg, $this->getName()), \E_USER_WARNING);
			return $this;
		}

		parent::setValue($value);

		return $this;
	}
}
