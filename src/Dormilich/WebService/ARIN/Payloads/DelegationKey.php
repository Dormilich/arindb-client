<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Integer;
use Dormilich\WebService\ARIN\Elements\Selection;
use Dormilich\WebService\ARIN\XMLHandler;

/**
 * The Delegation Key Payload is the portion of the Delegation Payload that 
 * contains algorithm name and digest type information.
 * 
 * The algorithm name and digest type name will be determined by the values 
 * you enter. You do not need to set the name on the payload. If you do, it 
 * will be discarded.
 */
class DelegationKey extends Payload
{
	public function __construct()
	{
		$this->name = 'delegationKey';
		$this->init();
	}

	protected function init()
	{
		// no explanation is given, which number represents which algorithm
		$this->create(new Selection('algorithm', [5, 7, 8]));
		// a hash value
		$this->create(new Element('digest', NULL, 'ctype_xdigit'));
		// guess: validity duration since submission in seconds
		$this->create(new Integer('ttl', 1));
		// guess: SHA1 & SHA2 family
		$this->create(new Selection('digestType', [1, 2]), 'type');
		// could be an integer, but no explanation is given
		$this->create(new Element('keyTag'));
	}

	public function toXML($encoding = 'UTF-8', $validate = XMLHandler::VALIDATE)
	{
		throw new \LogicException('This Delegation Key Payload should not be submitted by itself.');
	}
}
