<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Selection;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;
use Dormilich\WebService\ARIN\Lists\MultiLine;

/**
 * This payload allows the sending of additional information to an existing 
 * Ticket and to enable users to get a specific message and any accompanying 
 * attachment(s). The body of the payload will vary depending on the action 
 * requested.
 * 
 * The following fields are automatically completed by Reg-RWS, and should be 
 * left blank:
 *  - messageId
 *  - createdDate
 */
class Message extends Payload
{
	public function __construct()
	{
		$this->name = 'message';
		$this->init();
	}

	protected function init()
	{
		$uri = 'http://www.arin.net/regrws/messages/v1';
		// response only
		$this->create(new Element('ns2:messageId', $uri), 'id');
		// response only
		$this->create(new Element('ns2:createdDate', $uri), 'created');
		$this->create(new Element('subject'));
		$this->create(new MultiLine('text'));
		$this->create(new Selection('category', ['NONE', 'JUSTIFICATION']));
		// request only
		$this->create(new ObjectGroup('attachments', 'Attachment'));
		// response only
		$this->create(new ObjectGroup('attachmentReferences', 'AttachmentReference'));
	}

	public function isValid()
	{
		$id   = $this->get('id')->isValid();
		$date = $this->get('created')->isValid();
		$ref  = count($this->get('attachmentReferences'));

		if ($id or $date or $ref) {
			return false;
		}

		$subj = $this->get('subject')->isValid();
		$text = $this->get('text')->isValid();
		$cat  = $this->get('category')->isValid();
		$att  = $this->get('attachments')->isValid();

		return ($subj and $cat and ($text or $att));
	}
}
