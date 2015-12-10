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
				$this->padding = self::UNPADDED;
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
	 * Validate the input value against a validation function.
	 * 
	 * @param mixed $value Input value.
	 * @return boolean Boolean equivalent of the input value.
	 * @throws ConstraintException Validation failure.
	 */
	protected function validate($value)
	{
		// IPv4
		if (filter_var($this->unpad4($value), \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
			return $this->transformIP($value, 4);
		}
		// IPv6
		if (filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
			return $this->transformIP($value, 6);
		}
		// IPv4 as net number
		$options = ['min_range' => 0, 'max_range' => (1 << 32) - 1];
		$ipnum4  = filter_var($value, \FILTER_VALIDATE_INT, ['options' => $options]);
		if (is_int($ipnum4)) {
			return $this->transformIP(long2ip($value), 4);
		}

		$msg = 'Value "%s" is not a valid IP address in the [%s] element.';
		throw new ConstraintException(sprintf($msg, $value, $this->getName()));
	}

	/**
	 * Apply (un)padding to the IP address if the padding flag is set.
	 * 
	 * @param string $ip IP address.
	 * @param integer $version IP version.
	 * @return string Transformed IP address.
	 */
	protected function transformIP($value, $version)
	{
		if ($this->padding === self::PADDED) {
			return call_user_func([$this, 'pad'.$version], $value);
		}
		if ($this->padding === self::UNPADDED) {
			return inet_ntop(inet_pton($value));
		}
		return $value;
	}

	/**
	 * Convert unpadded IPv4 into padded IPv4.
	 * 
	 * @param string $ip Padded/unpadded IP address.
	 * @return string Padded IP address.
	 */
	protected function pad4($ip)
	{
		return vsprintf('%03d.%03d.%03d.%03d', explode('.', $ip));
	}

	/**
	 * Convert unpadded IPv6 into padded IPv6.
	 * 
	 * @param string $ip Padded/unpadded IPv6 address.
	 * @return string Padded IPv6 address.
	 */
	protected function pad6($ip)
	{
		$hex = bin2hex(inet_pton($ip));
		return implode(':', str_split($hex, 4));
	}

	/**
	 * Unpad any IPv4 address so it can be run through filter_var().
	 * Handling of padded IPv4 addresses is seemingly dependent on the 
	 * undelying OS. In contrast to transformIP() the input does not 
	 * need to be valid.
	 * 
	 * @param string $value IPv4 candidate string.
	 * @return string Unpadded IPv4 or original string.
	 */
	protected function unpad4($value)
	{
		if (preg_match('~^\d{1,3}(\.\d{1,3}){3}$~', $value)) {
			return vsprintf('%d.%d.%d.%d', explode('.', $value));
		}
		return $value;
	}
}
