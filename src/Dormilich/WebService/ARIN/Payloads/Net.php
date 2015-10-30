<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\FixedElement;
use Dormilich\WebService\ARIN\Elements\GroupElement;
use Dormilich\WebService\ARIN\Elements\MultilineElement;

class Net extends Payload
{
	public function __construct()
	{
		$this->name = 'net';
		$this->init();
	}

	protected function init()
	{
		$this->create(new FixedElement('version', [4, 6]));
		$this->create(new MultilineElement('comment'));
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Element('orgHandle'), 'org');
		$this->create(new Element('handle'));
		$this->create(new GroupElement('netBlocks'), 'net');
		$this->create(new Element('customerHandle'), 'customer');
		$this->create(new Element('parentNetHandle'), 'parentNet');
		$this->create(new Element('netName'));
		$this->create(new GroupElement('originASes'), 'AS');
		$this->create(new GroupElement('pocLinks'), 'poc');
	}
}
