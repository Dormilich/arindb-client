<?php

namespace Dormilich\WebService\ARIN;

/**
 * Provide methods to access elements in a group element by tag name.
 */
interface FilterInterface
{
	/**
	 * Get all elements whose tag name matches the given value.
	 * 
	 * @param mixed $name Tag name.
	 * @return array List of matching elements.
	 */
	public function filter($name);

	/**
	 * Get the first element whose tag name matches the given value.
	 * 
	 * @param mixed $name Tag name.
	 * @return object|NULL First matching element or NULL if no matching 
	 *          element was found.
	 */
	public function fetch($name);
}
