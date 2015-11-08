<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Boolean;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;

/**
 * The Ticket Payload contains details about a submitted Ticket. Some calls 
 * using this payload will be automatically processed. Others may require 
 * manual intervention by ARIN staff, in which case this payload will provide 
 * details regarding your request.
 * 
 * The following fields are automatically completed by Reg-RWS, and should be 
 * left blank:
 *  - ticketNo
 *  - createdDate
 *  - resolvedDate
 *  - closedDate
 *  - updatedDate
 *  - webTicketType
 *  - webTicketResolution
 * 
 * If you alter, modify, or omit these fields when performing a Ticket Modify, 
 * you will receive an error.
 */
class Ticket extends Payload
{
	public function __construct($number = NULL)
	{
		$this->name = 'ticket';
		$this->init();
		$this->set('ticketNo', $number);
	}

	protected function init()
	{
		$uri = 'http://www.arin.net/regrws/shared-ticket/v1';

		$this->create(new ObjectGroup('messages', 'Message'));
		$this->create(new ObjectGroup('messageReferences', 'MessageReference'));
		$this->create(new Element('ticketNo'));
		$this->create(new Boolean('ns4:shared', $uri));
		$this->create(new Element('ns4:orgHandle', $uri));
		$this->create(new Element('createdDate'), 'created');
		$this->create(new Element('resolvedDate'), 'resolved');
		$this->create(new Element('closedDate'), 'closed');
		$this->create(new Element('updatedDate'), 'updated');
		$this->create(new Element('webTicketType'), 'type');
		$this->create(new Element('webTicketStatus'), 'status');
		$this->create(new Element('webTicketResolution'), 'resolution');
	}

	public function isValid()
	{
		// only the status may be changed to "closed"
		return $this->get('status')->getValue() === 'CLOSED' and $this->fixedModify();
	}

	private function fixedModify()
	{
		$fixed = $this->filter('ticketNo', 'createdDate', 'resolvedDate', 
			'closedDate', 'updatedDate', 'webTicketType', 'webTicketResolution');

		return array_reduce($fixed, function ($carry, $item) {
			return $carry and $item->isValid();
		}, true);
	}
}

/* 
Web Ticket Resolution:

ACCEPTED, DENIED, ABANDONED, ANSWERED, PROCESSED, DUPLICATE, UNSUCCESSFUL, OTHER

Web Ticket Status: 

PENDING_CONFIRMATION, PENDING_REVIEW, ASSIGNED, IN_PROGRESS, WAIT_LIST, RESOLVED, 
CLOSED, APPROVED, ANY, ANY_OPEN

Web Ticket Type:

QUESTION, ASSOCIATIONS_REPORT, REASSIGNMENT_REPORT, WHOWAS_REPORT, WHOWAS_ACCESS, 
ORG_CREATE, EDIT_ORG_NAME, ORG_RECOVERY, TRANSFER_PREAPPROVAL, TRANSFER_RECIPIENT_82, 
TRANSFER_RECIPIENT_83, TRANSFER_SOURCE_83, TRANSFER_LISTING_SERVICE, IPV4_SIMPLE_REASSIGN, 
IPV4_DETAILED_REASSIGN, IPV4_REALLOCATE, IPV6_DETAILED_REASSIGN, IPV6_REALLOCATE, 
NET_DELETE_REQUEST, ISP_IPV4_REQUEST, ISP_IPV6_REQUEST, CREATE_RESOURCE_CERTIFICATE, 
CREATE_ROA, END_USER_IPV4_REQUEST, END_USER_IPV6_REQUEST, ASN_REQUEST, EDIT_BILLING_CONTACT_INFO, ANY
*/