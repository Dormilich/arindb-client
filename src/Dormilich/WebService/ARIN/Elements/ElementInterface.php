<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\DOMSerializable;

/**
 * An Element refers to either a single XML element having a text value 
 * or a collection of other elements.
 */
interface ElementInterface extends DOMSerializable
{
	/**
	 * Get the data of the element. This may be a string or an array of 
	 * strings.
	 * 
	 * @return string|array(string) 
	 */
	public function getValue();

	/**
	 * Set the value of the element. If the element is an array type, delete 
	 * previously set values. If the element is a Payload then the behaviour 
	 * is implementation dependent and may throw an exception.
	 * 
	 * @param mixed $value Element value.
	 * @return void|self
	 */
	public function setValue($value);

	/**
	 * Add a value to the element. If the element is a collection type, append 
	 * the value to the existing collection, otherwise the behaviour depends 
	 * on the concrete element.
	 * 
	 * @param mixed $value Element value.
	 * @return void|self
	 */
	public function addValue($value);
}
