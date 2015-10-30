<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;

class NetBlock extends Payload implements DOMSerializable
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
