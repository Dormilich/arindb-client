<?php

namespace Dormilich\WebService\ARIN\Elements;

use Dormilich\WebService\ARIN\Exceptions\ConstraintException;

/**
 * This class represents an XML element that contains an IP address.
 */
class IP extends Element
{
	/**
	 * @var UNPADDED Flag to convert IPv4 addresses to unpadded format.
	 */
	const UNPADDED = false;

	/**
	 * @var PADDED Flag to convert IPv4 addresses to padded format.
	 */
	const PADDED   = true;

	/**
	 * @var mixed $padding Value of the default padding conversion setting.
	 */
	protected $padding;

	/**
	 * Set up the element and optionally configure the default IPv4 padding 
	 * conversion flag. The default setting is to not convert padding either way.
	 * 
	 * @param string $name Tag name.
	 * @param string $ns (optional) Namespace URI.
	 * @param mixed $flag Padding conversion flag.
	 * @return self
     * @throws LogicException Namespace prefix missing.
	 */
	public function __construct($name, $ns = NULL)
	{
		$this->setNamespace((string) $name, $ns);

		$args = array_slice(func_get_args(), 1, 2);

		if ($this->namespace) {
			array_shift($args);
		}

		if (isset($args[0])) {
			if (self::PADDED === $args[0]) {
				$this->padding = self::PADDED;
			}
			elseif (self::UNPADDED === $args[0]) {
				$this->padding = false;
			}
		}
	}

	/**
	 * Set IP value. If a padding flag is provided, convert the IP address 
	 * according to that flag.
	 * 
	 * @param string $value New IP address.
	 * @param mixed $flag Flag for applying padding to IPv4 addresses.
	 * @return self
	 */
	public function setValue($value)
	{
		// save original value
		$padding = $this->padding;

		// use provided value
		if (func_num_args() > 1) {
			$this->padding = func_get_arg(1);
		}

		parent::setValue($value);

		// restore original value
		$this->padding = $padding;

		return $this;
	}

	/**
	 * Validates input as IP address.
	 * 
	 * @param string $value 
	 * @return string
	 */
	protected function convert($value)
	{
		$value = parent::convert($value);
		$unpadded = $this->unpad($value);

		if (!filter_var($unpadded, \FILTER_VALIDATE_IP)) {
			$msg = 'Value "%s" is not a valid IP address in the [%s] element.';
			throw new ConstraintException(sprintf($msg, $value, $this->name));
		}

		if ($this->padding === self::PADDED) {
			return $this->pad($value);
		}
		if ($this->padding === self::UNPADDED) {
			return $unpadded;
		}
		return $value;
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
		if (strpos($ip, '.') === false) {
			return $ip;
		}
		$list = explode('.', $ip);
		$tpl  = implode('.', array_fill(0, count($list), '%d'));

		return vsprintf($tpl, $list);
	}

	/**
	 * Convert unpadded IPv4 into padded IPv4.
	 * 
	 * @param string $ip Padded/unpadded IP address.
	 * @return string Padded IP address.
	 */
	protected function pad($ip)
	{
		if (strpos($ip, '.') === false) {
			return $ip;
		}
		$list = explode('.', $ip);
		$tpl  = implode('.', array_fill(0, count($list), '%03d'));

		return vsprintf($tpl, $list);
	}
}