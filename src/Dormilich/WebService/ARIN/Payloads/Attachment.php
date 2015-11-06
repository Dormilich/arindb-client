<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;

/**
 * This payload allows you to add attachments to an existing Ticket as part of 
 * an Add Message call. 
 * 
 * This Attachment Payload should not be submitted by itself.
 */
class Attachment extends Payload
{
	public function __construct()
	{
		$this->name = 'attachment';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('data'));
		$this->create(new Element('filename'));
	}

	/**
	 * It is not exactly clear when an attachment payload is valid, although 
	 * the definition suggests that any defined element suffices.
	 */
	public function isValid()
	{
		return array_reduce($this->elements, function ($carry, $item) {
			return $carry or $item->isValid();
		}, false);
	}

	public function toXML()
	{
		throw new \LogicException('This Attachment Payload should not be submitted by itself.');
	}
}
