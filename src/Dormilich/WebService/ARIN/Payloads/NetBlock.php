<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Integer;
use Dormilich\WebService\ARIN\Elements\IP;
use Dormilich\WebService\ARIN\Elements\Selection;
use Dormilich\WebService\ARIN\XMLHandler;

/**
 * The NET Block Payload contains details on the NET Block of the Network 
 * specified. The NET Block Payload is a nested element of a NET Payload. 
 * See NET Payload for additional details.
 * 
 * When submitting a NET Block Payload as part of the NET Payload, the IP 
 * addresses provided in the startAddress and endAddress elements can be 
 * non-zero-padded (i.e. 10.0.0.255) or zero-padded (i.e. 010.000.000.255). 
 * The payload returned will always express IP addresses as zero-padded.
 * 
 * The description field will be determined by the type you specify, and may 
 * be left blank.
 */
class NetBlock extends Payload
{
	public function __construct($flag = IP::UNPADDED)
	{
		$this->name = 'netBlock';
		$this->init($flag);
	}

	protected function init()
	{
		$abbr = [
			'A',  'AF', 'AP', 'AR', 'AV', 'DA', 'DS', 'FX', 'IR', 'IU', 
			'LN', 'LX', 'PV', 'PX', 'RD', 'RN', 'RV', 'RX', 'S', 
		];
		$this->create(new Selection('type', $abbr));
		$this->create(new Element('description'));
		$flag = func_get_arg(0); // can’t change method parameters
		$this->create(new IP('startAddress', $flag), 'start');
		$this->create(new IP('endAddress', $flag), 'end');
		$this->create(new Integer('cidrLength', 0, 128), 'length');
	}

	public function isValid()
	{
		$type  = $this->get('type')->isValid();
		$start = $this->get('start')->isValid();
		$end   = $this->get('end')->isValid();
		$cidr  = $this->get('length')->isValid();

		return $type and $start and ($end or $cidr);
	}

	public function toXML($encoding = 'UTF-8', $validate = XMLHandler::VALIDATE)
	{
		throw new \LogicException('This Net Block Payload should not be submitted by itself.');
	}

	public function __toString()
	{
		$start  = $this->get('start');
		$length = $this->get('length');

		if ($start->isValid() and $length->isValid()) {
			return inet_ntop(inet_pton($start->getValue())) . '/' . $length;
		}
		return '';
	}
}
