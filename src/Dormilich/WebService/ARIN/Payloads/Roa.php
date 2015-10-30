<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;

class Roa extends Payload
{
	public function __construct()
	{
		$this->name = 'roa';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Element('roaData'));
		$this->create(new Element('signature'));
	}
}
