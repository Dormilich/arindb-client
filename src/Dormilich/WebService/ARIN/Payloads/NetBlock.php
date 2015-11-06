<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Integer;
use Dormilich\WebService\ARIN\Elements\IP;
use Dormilich\WebService\ARIN\Elements\Selection;

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
		$this->create(new Selection('type', $abbr));
		$this->create(new Element('description'));
		$this->create(new IP('startAddress', IP::UNPADDED), 'start');
		$this->create(new IP('endAddress', IP::UNPADDED), 'end');
		$this->create(new Integer('cidrLength', 0, 128), 'cidr');
	}

	public function isValid()
	{
		$start = $this->get('start')->isValid();
		$end   = $this->get('end')->isValid();
		$cidr  = $this->get('cidr')->isValid();

		return $start and ($end or $cidr);
	}
}
