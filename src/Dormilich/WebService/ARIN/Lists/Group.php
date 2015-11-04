<?php

namespace Dormilich\WebService\ARIN\Lists;

use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class accepts any serialisable object(s) as its content.
 * The main use of this class is to provide a container for nested payloads.
 */
class Group extends ArrayElement
{
	/**
	 * Check if any member of the collection is defined.
	 * 
	 * @return boolean
	 */
	public function isDefined()
	{
		$bool = array_map(function ($item) {
			return $item->isDefined();
		}, $this->value);

		return array_reduce($bool, function ($carry, $item) {
			return $carry or $item;
		}, false);
	}

	/**
	 * Check if the value is a serialisable element.
	 * 
	 * @param mixed $value 
	 * @return XMLHandler
	 * @throws Exception Value is not serialisable.
	 */
	protected function convert($value)
	{
		if ($value instanceof XMLHandler) {
			return $value;
		}
		$msg = 'Value of type %s is not a valid object for the [%s] element.';
		$type = is_object($value) ? get_class($value) : gettype($value);
		throw new DataTypeException(sprintf($msg, $type, $this->name));
	}
}
