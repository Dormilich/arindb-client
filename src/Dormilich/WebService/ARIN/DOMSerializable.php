<?php

namespace Dormilich\WebService\ARIN;

/**
 * This interface shall guarantee that an element can be transformed into an 
 * XML element.
 */
interface DOMSerializable
{
	/**
	 * The tag name of the base XML element.
	 * 
	 * @return string
	 */
	public function getName();

	/**
	 * Determines if an element is empty or valid. The XML serialiser may 
	 * choose to omit empty elements in the serialisation process.
	 * 
	 * @return boolean TRUE if the element scontains (sufficient) data.
	 */
	public function isDefined();

	/**
	 * Transform the element into its XML representation.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	public function toDOM(\DOMDocument $doc);
}
