<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Lists\ObjectGroup;

/**
 * The Ticketed Request Payload details about a Ticket and or NET affected by 
 * that Ticket. If the call you are using may result in a NET being returned 
 * or a Ticket being returned, a Ticketed Request Payload is returned. This 
 * may occur when performing a reallocation/reassignment. If your reallocation 
 * or reassignment meets the conditions for automatic processing, the Ticketed 
 * Request Payload will have an embedded NET Payload representing the NET that 
 * was created. If your reallocation or reassignment does not meet the 
 * conditions for automatic processing, the Ticket Request Payload will have 
 * an embedded Ticket Payload representing the Ticket that was created for 
 * your request. See NET Reassign and NET Reallocate for more details.
 */
class TicketedRequest extends ObjectGroup implements \JsonSerializable
{
	public function __construct()
	{
		parent::__construct('ticketedRequest', ['Ticket', 'Net']);
	}

	/**
	 * @see http://php.net/JsonSerializable
	 */
	public function jsonSerialize()
	{
		return $this->getValue(true);
	}
}
