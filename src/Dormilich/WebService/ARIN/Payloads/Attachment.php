<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\DOMSerializable;
use Dormilich\WebService\ARIN\Elements\Element;

class Attachment extends Payload implements DOMSerializable
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

	public function isDefined()
	{
		$data = $this->getAttribute('data')->isDefined();
		$file = $this->getAttribute('filename')->isDefined();

		return $data or $file;
	}
}
