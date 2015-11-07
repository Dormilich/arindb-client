<?php

namespace Dormilich\WebService\ARIN;

/**
 * This interface shall guarantee that an element can be transformed into an 
 * XML element.
 */
interface XMLHandler
{
	/**
	 * Do (not) validate the payload before creating the request XML.
	 */
	const VALIDATE   = true;
	const NOVALIDATE = false;

	/**
	 * The tag name of the base XML element.
	 * 
	 * @return string
	 */
	public function getName();

	/**
	 * Determines if an element is valid. The XML serialiser may 
	 * choose to omit empty elements in the serialisation process.
	 * 
	 * @return boolean TRUE if the element scontains (sufficient) data.
	 */
	public function isValid();

	/**
	 * Transform the element into its XML representation.
	 * 
	 * @param DOMDocument $doc 
	 * @return DOMElement
	 */
	public function toDOM(\DOMDocument $doc);

	/**
	 * Read the data from the xml node(s) into the object.
	 * 
	 * @param SimpleXMLElement $sxe 
	 * @return void
	 */
	public function parse(\SimpleXMLElement $sxe);
}
