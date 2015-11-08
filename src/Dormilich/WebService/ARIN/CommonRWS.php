<?php
// CommonRWS.php

namespace Dormilich\WebService\ARIN;

use Dormilich\WebService\Adapter\ClientAdapter;
use Dormilich\WebService\ARIN\XMLHandler;
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
class CommonRWS
{
	const SANDBOX         = 'test';

	const PRODUCTION      = 'live';

	const SANDBOX_HOST    = 'https://reg.ote.arin.net/rest/';

	const PRODUCTION_HOST = 'https://reg.arin.net/rest/';

	private $config       = [];

	protected $results    = [];

	protected $client;

	/**
	 * Create a webservice to request WHOIS data. 
	 * 
	 * @param ClientAdapter $client A connection adapter.
	 * @param array $config Webservice config options 
	 * @return self
	 */
	public function __construct(ClientAdapter $client, array $config = array())
	{
		$this->client = $client;

		$this->setOptions($config);

		$base = $this->isProduction() ? self::PRODUCTION_HOST : self::SANDBOX_HOST;
		$this->client->setBaseUri($base);
	}

	/**
	 * Set the config options.
	 * 
	 * @param type array $options 
	 * @return type
	 */
	protected function setOptions(array $options)
	{
		$defaults = [
			'environment' => self::SANDBOX,
			'password'    => '', 
			'strict'      => XMLHandler::VALIDATE,
			'encoding'    => 'UTF-8',
		];
		$this->config = $options + $defaults;
	}

	/**
	 * Whether the live database is used.
	 * 
	 * @return boolean
	 */
	public function isProduction()
	{
		return strtolower($this->config['environment']) === self::PRODUCTION;
	}

	/**
	 * Pass the request data to the connection object.
	 * 
	 * @param string $method HTTP method.
	 * @param string $path Request specific URL path.
	 * @param array $query Any URL parameters.
	 * @param Payload|NULL $body The payload object.
	 * @return Payload The parsed response.
	 */
	protected function submit($method, $path, array $options = array(), Payload $body = NULL)
	{
		if ($body) {
			$body = $body->toXML($this->config['encoding'], $this->config['strict'])->saveXML();
		}
		if (count($options)) {
			$path .= ';' . http_build_query($options, '', ';', \PHP_QUERY_RFC3986);
		}
		$path .= '?apikey=' . rawurlencode($this->config['password']);

		$xml = $this->client->request($method, $path, $body);

		return Payload::loadXML($xml);
	}

	/**
	 * Request a Customer, Net, Org, or Poc resource. The request payload only 
	 * needs the 'handle' property to be set.
	 * 
	 * @param Payload $payload 
	 * @return Payload
	 */
	public function read(Payload $payload)
	{
		return $this->submit('GET', $payload->getName() . '/' . $payload['handle']);
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
		return $this->submit('PUT', $payload->getName() . '/' . $payload['handle'], [], $payload);
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
		return $this->submit('DELETE', $payload->getName() . '/' . $payload['handle']);
	}

	/**
	 * Create a Customer, Org, or Poc resource. Depending on the object, you 
	 * may need to pass additional information.
	 *  - Poc + make link: assign Poc to your account
	 *  - Org + net handle: assign the Org to a net
	 *  - Customer + net handle: the parent net handle is required
	 * 
	 * @param Payload $payload 
	 * @param mixed $param 
	 * @return Payload
	 * @throws Exception Payload is not a Customer, Org, or Poc.
	 * @throws Exception Parent net handle missing for Customer.
	 */
	public function create(Payload $payload, $param = NULL)
	{
		if ($param instanceof Net) {
			$param = $param['handle'];
		}

		if ($payload instanceof Customer) {
			if ($param) {
				return $this->submit('POST', sprintf('net/%s/customer', $param), [], $payload);
			}
			throw new RequestException('Parent net handle missing.');
		}

		if ($payload instanceof Org) {
			if ($param) {
				return $this->submit('POST', sprintf('net/%s/org', $param), [], $payload);
			}
			return $this->submit('POST', 'org', [], $payload);
		}

		if ($payload instanceof Poc) {
			return $this->submit('POST', 'poc', [
				'makeLink' => $this->bool2string($param)
			], $payload);
		}

		throw new RequestException('Object of type '.$payload->getName().' does not support direct creation.');
	}

	/**
	 * Assign a subnet to a customer.
	 * 
	 * @see https://www.arin.net/resources/restfulmethods.html#netreassign
	 * 
	 * @param Net $payload 
	 * @param string $parentNet 
	 * @return TicketedRequest
	 */
	public function assign(Net $payload, $parentNet)
	{
		return $this->submit('POST', sprintf('net/%s/reassign', $parentNet), [], $payload);
	}

	/**
	 * Assign a subnet to an organisation.
	 * 
	 * @see https://www.arin.net/resources/restfulmethods.html#netreallocate
	 * 
	 * @param Net $payload 
	 * @param string $parentNet 
	 * @return TicketedRequest
	 */
	public function allocate(Net $payload, $parentNet)
	{
		return $this->submit('POST', sprintf('net/%s/reallocate', $parentNet), [], $payload);
	}

	/**
	 * Convert a value into its boolean equivalents "true" resp. "false".
	 * 
	 * @param mixed $value 
	 * @return string Boolean string.
	 */
	public function bool2string($value)
	{
		return filter_var($value, \FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
	}
}
