<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;

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
	public function __construct()
	{
		$this->name = 'netBlock';
		$this->init();
	}

	protected function init()
	{
		$abbr = [
			'A',  'AF', 'AP', 'AR', 'AV', 'DA', 'DS', 'FX', 'IR', 'IU', 
			'LN', 'LX', 'PV', 'PX', 'RD', 'RN', 'RV', 'RX', 'S', 
		];
		$this->create(new FixedElement('type', $abbr));
		$this->create(new Element('description'));
		$this->create(new Element('startAddress'), 'start');
		$this->create(new Element('endAddress'), 'end');
		$this->create(new Element('cidrLength'), 'cidr');
	}

	public function isDefined()
	{
		$start = $this->getAttribute('start')->isDefined();
		$end   = $this->getAttribute('end')->isDefined();
		$cidr  = $this->getAttribute('cidr')->isDefined();

		return $start and ($end or $cidr);
	}
}
