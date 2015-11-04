<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\DataTypeException;

/**
 * This class represents an XML element that contains an IP address.
 */
class IP extends Element
{
	/**
	 * Validates input as IP address.
	 * 
	 * @param string $value 
	 * @return string
	 */
	protected function convert($value)
	{
		$value = parent::convert($value);

		if (filter_var($this->unpad($value), \FILTER_VALIDATE_IP)) {
			return $value;
		}
        $msg = 'Value "%s" is not a valid IP address in the [%s] element.';
        throw new DataTypeException(sprintf($msg, $value, $this->name));
	}

	/**
	 * Convert padded IPv4 into unpadded IPv4 since the IP filter may not work 
	 * on padded IPv4.
	 * Tests showed that explode/implode is 2-3 times faster than preg_replace.
	 * 
	 * @param string $ip Padded/unpadded IP address.
	 * @return string Unpadded IP address.
	 */
	protected function unpad($ip)
	{
		$list = explode('.', $ip);
		$tpl  = implode('.', array_fill(0, count($list), '%d'));

		return vsprintf($tpl, $list);
	}
}
