<?php
// CommonRWS.php

namespace Dormilich\WebService\ARIN;

use Dormilich\WebService\ARIN\Primary;
use Dormilich\WebService\ARIN\Exceptions\RequestException;
use Dormilich\WebService\ARIN\Payloads\Payload;
use Dormilich\WebService\ARIN\Payloads\Customer;
use Dormilich\WebService\ARIN\Payloads\Delegation;
use Dormilich\WebService\ARIN\Payloads\Net;
use Dormilich\WebService\ARIN\Payloads\Org;
use Dormilich\WebService\ARIN\Payloads\Poc;
use Dormilich\WebService\ARIN\Payloads\Phone;
use Dormilich\WebService\ARIN\Payloads\PhoneType;

/**
 * Handling of ARIN CRUD operations.
 */
class CommonRWS extends WebServiceSetup
{
	/**
	 * Get the (base) path for a primary payload.
	 * 
	 * @param Primary $payload 
	 * @return string
	 */
	private function getPath(Primary $payload)
	{
		return $payload->getName() . '/' . $payload->getHandle();
	}

	/**
	 * Request a Delegation, Customer, Net, Org, or Poc resource. The request 
	 * payload only needs the lookup key to be set, which can be done through 
	 * the constructor.
	 * 
	 * @param Primary $payload 
	 * @return Payload
	 */
	public function read(Primary $payload)
	{
		return $this->submit('GET', $this->getPath($payload));
	}

	/**
	 * Modify a Delegation, Customer, Net, Org, or Poc resource. The request 
	 * payload only needs the lookup key to be set, which can be done through 
	 * the constructor.
	 * 
	 * @param Primary $payload 
	 * @return Payload
	 */
	public function update(Primary $payload)
	{
		return $this->submit('PUT', $this->getPath($payload), [], $payload);
	}

	/**
	 * Remove a Customer, Net, Org, or Poc resource. The request payload only 
	 * needs the lookup key to be set, which can be done through the constructor. 
	 * For Customer, Net, and Org resources the additional parameters are ignored.
	 * 
	 * If a Delegation payload is given, delete all its nameservers. If the 
	 * Delegation payload is added a nameserver (string) then this particular 
	 * nameserver is removed.
	 * 
	 * If a Poc payload is given with additional Parameters then the appropriate 
	 * resources (phone, email) are deleted (@see CommonRWS::parseParam()).
	 * 
	 * If a Net delete cannot be automatically processed, a Ticket is issued.
	 * If a Net payload contains a customer handle, it is tried to delete the 
	 * customer resource first.
	 * 
	 * Examples:
	 *  - Customer              => customer resource
	 *  - Net                   => network resource (via TicketedRequest)
	 *  - Org                   => org resource
	 *  - Poc                   => poc resource
	 *  - Poc + Phone           => phone number
	 *  - Poc + PhoneType       => all phone numbers with that type
	 *  - Poc + Phone + 'type'  => phone number with that type
	 *  - Poc + 'type'          => all phone numbers with that type
	 *  - Poc + 'phone number'  => phone number
	 *  - Delegation            => all nameservers
	 *  - Delegation + 'server' => nameserver
	 * 
	 * @param Primary $payload 
	 * @param mixed $param 
	 * @param string $type 
	 * @return Payload
	 */
	public function delete(Primary $payload, $param = false, $type = false)
	{
		$path = $this->getPath($payload);

		if ($payload instanceof Net) {
			if ($payload['customer']->isValid()) {
				$this->delete(new Customer($payload['customer']));
			}
		}

		if ($param and $payload instanceof Poc) {
			$path .= $this->parseParam($param);
			if (strlen($type) === 1 and strpos($path, '/phone/')) {
				$path .= ';type=' . strtoupper($type);
			}
		}

		if ($payload instanceof Delegation) {
			if ($param) {
				$path .= '/nameserver/' . $param;
			}
			else {
				$path .= '/nameservers';
			}
		}

		return $this->submit('DELETE', $path);
	}

	/**
	 * Create a Customer, Net, Org, or Poc resource. Depending on the object, 
	 * you may need to pass additional information.
	 * 
	 * Note: Whether a Net is reassigned or reallocated is determined by the 
	 * existence of a customer or org handle. Additionally, the parent net 
	 * handle must be set.
	 * 
	 * If a Net allocation/assignment cannot be automatically processed, 
	 * a Ticket is issued.
	 * 
	 * Examples:
	 *  - Net                            => network (via TicketedRequest)
	 *  - Org                            => org (via Ticket)
	 *  - Org + Net                      => org for reallocation
	 *  - Org + 'parent-net-handle'      => org for reallocation
	 *  - Customer + Net                 => customer for reassignment
	 *  - Customer + 'parent-net-handle' => customer for reassignment
	 *  - Poc [ + false ]                => immutable Poc
	 *  - Poc + true                     => editable Poc
	 * 
	 * @param Primary $payload 
	 * @param mixed $param 
	 * @return Payload
	 * @throws RequestException Payload is not a Customer, Net, Org, or Poc.
	 * @throws RequestException Parent net handle missing for Customer.
	 */
	public function create(Primary $payload, $param = NULL)
	{
		if ($param instanceof Net) {
			$param = $param->getHandle();
		}

		if ($payload instanceof Customer) {
			return $this->createCustomer($payload, $param);
		}

		if ($payload instanceof Net) {
			return $this->createNet($payload);
		}

		if ($payload instanceof Org) {
			return $this->createOrg($payload, $param);
		}

		if ($payload instanceof Poc) {
			return $this->createPoc($payload, $param);
		}

		throw new RequestException('Object of type '.$payload->getName().' does not support direct creation.');
	}

	/**
	 * Add a phone or email to the poc resource or a nameserver to the 
	 * delegation resource.
	 * 
	 * Examples:
	 *  - Poc + Phone               => phone
	 *  - Poc + 'email'             => email
	 *  - Delegation + 'server'     => nameserver
	 * 
	 * @param Primary $payload A Poc or delegation payload.
	 * @param mixed $param Phone payload or email address.
	 * @return Payload A Phone (phone) or Poc (email) payload.
	 * @throws RequestException Invalid input.
	 */
	public function add(Primary $payload, $param)
	{
		if ($payload instanceof Poc) {
			return $this->addPocData($payload, $param);
		}

		if ($payload instanceof Delegation) {
			return $this->addNameserver($payload, $param);
		}

		throw new RequestException('Invalid input given.');
	}

	/**
	 * Parse the delete() param when determining the path for deleting an email 
	 * or phone from a Poc. Returns an email path if the param is an email 
	 * address, phone type path for a PhoneType payload or s single character, 
	 * and a phone number path for a Phone payload or anything else.
	 * 
	 * @param mixed $param 
	 * @return string
	 */
	private function parseParam($param)
	{
		if (filter_var($param, \FILTER_VALIDATE_EMAIL)) {
			return '/email/' . $param;
		}

		if ($param instanceof Phone) {
			return '/phone/' . $param['number'];
		}

		if ($param instanceof PhoneType) {
			return '/phone/type=' . $param['code'];
		}

		if (strlen($param) === 1) {
			return '/phone/type=' . strtoupper($param);
		}

		return '/phone/' . $param;
	}

	/**
	 * Add a phone or email resource.
	 * 
	 * @param Poc $payload Poc payload.
	 * @param string|Phone $param Phone number or email address.
	 * @return Poc
	 */
	private function addPocData(Poc $payload, $param)
	{
		$path = $this->getPath($payload);

		if ($param instanceof Phone) {
			$path .= '/phone';
			return $this->submit('PUT', $path, [], $param);
		}

		if (filter_var($param, \FILTER_VALIDATE_EMAIL)) {
			$path .= '/email/' . $param;
			return $this->submit('POST', $path);
		}
	}

	/**
	 * Add a nameserver.
	 * 
	 * @param Delegation $payload Delegation payload.
	 * @param string $param Nameserver.
	 * @return Delegation
	 */
	private function addNameserver(Delegation $payload, $param)
	{
		$path  = $this->getPath($payload);
		$path .= '/nameserver/' . $param;

		return $this->submit('POST', $path);
	}

	/**
	 * Create a customer resource.
	 * 
	 * @param Customer $payload Customer payload.
	 * @param string $param Parent net handle.
	 * @return Customer
	 */
	private function createCustomer(Customer $payload, $param)
	{
		if (!$param) {
			throw new RequestException('Parent net handle missing.');
		}
		$path = sprintf('net/%s/customer', $param);
		return $this->submit('POST', $path, [], $payload);
	}

	/**
	 * Create a network resource.
	 * 
	 * @param Net $payload Net payload.
	 * @return TicketedRequest
	 */
	private function createNet(Net $payload)
	{
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

	/**
	 * Create an organisation resource.
	 * 
	 * @param Org $payload Org payload.
	 * @param string $param Parent net handle.
	 * @return Org|Ticket
	 */
	private function createOrg(Org $payload, $param)
	{
		$path = 'org';
		if ($param) {
			$path = sprintf('net/%s/org', $param);
		}
		return $this->submit('POST', $path, [], $payload);
	}

	/**
	 * Create a poc resource.
	 * 
	 * @param Poc $payload Poc payload.
	 * @param boolean $param TRUE if the poc should be linked to the account.
	 * @return Poc
	 */
	private function createPoc(Poc $payload, $param)
	{
		$path = 'poc;makeLink=' . $this->bool2string($param);
		return $this->submit('POST', $path, [], $payload);
	}
}
