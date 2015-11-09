<?php
// CommonRWS.php

namespace Dormilich\WebService\ARIN;

use Dormilich\WebService\ARIN\Exceptions\RequestException;
use Dormilich\WebService\ARIN\Payloads\Payload;
use Dormilich\WebService\ARIN\Payloads\Customer;
use Dormilich\WebService\ARIN\Payloads\Net;
use Dormilich\WebService\ARIN\Payloads\Org;
use Dormilich\WebService\ARIN\Payloads\Poc;

/**
 * Basic web service that can handle simple requests using the more straightforward 
 * objects Customer, Net, Poc, and Org that have a HANDLE.
 */
class CommonRWS extends WebServiceSetup
{

	/**
	 * Request a Customer, Net, Org, or Poc resource. The request payload only 
	 * needs the 'handle' property to be set.
	 * 
	 * @param Payload $payload 
	 * @return Payload
	 */
	public function read(Payload $payload)
	{
		$path = $payload->getName() . '/' . $payload['handle'];
		return $this->submit('GET', $path);
	}

	/**
	 * Modify a Customer, Net, Org, or Poc resource. The request payload only 
	 * needs the 'handle' property to be set.
	 * 
	 * @param Payload $payload 
	 * @return Payload
	 */
	public function update(Payload $payload)
	{
		$path = $payload->getName() . '/' . $payload['handle'];
		return $this->submit('PUT', $path, [], $payload);
	}

	/**
	 * Remove a Customer, Net, Org, or Poc resource. The request payload only 
	 * needs the 'handle' property to be set.
	 * 
	 * @param Payload $payload 
	 * @return Payload
	 */
	public function delete(Payload $payload)
	{
		$path = $payload->getName() . '/' . $payload['handle'];
		return $this->submit('DELETE', $path);
	}

	/**
	 * Create a Customer, Net, Org, or Poc resource. Depending on the object, 
	 * you may need to pass additional information.
	 *  - Poc + make link: assign Poc to your account
	 *  - Org + net handle: assign the Org to a net
	 *  - Customer + net handle: the parent net handle is required
	 * 
	 * @param Payload $payload 
	 * @param mixed $param 
	 * @return Payload
	 * @throws RequestException Payload is not a Customer, Net, Org, or Poc.
	 * @throws RequestException Parent net handle missing for Customer.
	 */
	public function create(Payload $payload, $param = NULL)
	{
		if ($param instanceof Net) {
			$param = $param['handle'];
		}

		if ($payload instanceof Customer) {
			if (!$param) {
				throw new RequestException('Parent net handle missing.');
			}
			$path = sprintf('net/%s/customer', $param);
			return $this->submit('POST', $path, [], $payload);
		}

		if ($payload instanceof Net) {
			if (!$payload['parentNet']->isValid()) {
				throw new RequestException('Parent Net handle is not defined.');
			}

			if ($payload['customer']->isValid()) {
				$path = sprintf('net/%s/reassign', $payload['parentNet']);
			}
			elseif ($payload['org']->isValid()) {
				$path = sprintf('net/%s/reallocate', $payload['parentNet']);
			}
			else {
				throw new RequestException('Customer/Org handle is not defined.');
			}

			return $this->submit('POST', $path, [], $payload);
		}

		if ($payload instanceof Org) {
			$path = 'org';
			if ($param) {
				$path = sprintf('net/%s/org', $param);
			}
			return $this->submit('POST', $path, [], $payload);
		}

		if ($payload instanceof Poc) {
			return $this->submit('POST', 'poc', [
				'makeLink' => $this->bool2string($param)
			], $payload);
		}

		throw new RequestException('Object of type '.$payload->getName().' does not support direct creation.');
	}
}
