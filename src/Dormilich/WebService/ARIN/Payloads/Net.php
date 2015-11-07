<?php

namespace Dormilich\WebService\ARIN\Payloads;

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Elements\Selection;
use Dormilich\WebService\ARIN\Lists\MultiLine;
use Dormilich\WebService\ARIN\Lists\NamedGroup;
use Dormilich\WebService\ARIN\Lists\ObjectGroup;

/**
 * The NET Payload contains details about an IPv4 or IPv6 network.
 * 
 * If you send a NET Payload and need to fill in the netBlock field, only 
 * either the endAddress or the cidrLength fields are required; not both. 
 * Reg-RWS will calculate the other for you, and the details for both will be 
 * returned in any call resulting in a NET Payload.
 * 
 * If you specify a NET type, it must be one of the valid codes located in the 
 * table under NET Block Payload. If you do not provide a type, Reg-RWS will 
 * determine it for you, depending on which call you are using. The version 
 * field may contain a value of "4" or "6," depending on the type of NET you 
 * are referring to. If left blank, this field will be completed for you based 
 * on the startAddress.
 * 
 * When submitting a NET Payload, the IP addresses provided in the 
 * startAddress and endAddress fields can be non-zero-padded (i.e. 10.0.0.255) 
 * or zero-padded (i.e. 010.000.000.255). The payload returned will always 
 * express IP addresses as zero-padded.
 * 
 * The comment field can be used to display operational information about the 
 * Customer (NOC hours, website, etc.). All comments must be accurate and 
 * operational in nature. ARIN reserves the right to edit or remove public 
 * comments.
 * 
 * The following fields are automatically filled in once you submit the 
 * payload, and should be left blank:
 *  - handle
 *  - registrationDate
 * 
 * If you alter or omit these fields when performing a NET Modify, you will 
 * receive an error.
 * 
 * The orgHandle and customerHandle elements are mutually exclusive. Depending 
 * on the type of the call this payload is being used for, you are required to 
 * assign either a Customer or an ORG. One of the two values will be present 
 * at all times.
 * 
 * The following fields may not be modified during a NET Modify:
 *  - version
 *  - orgHandle
 *  - netBlock
 *  - customerHandle
 *  - parentNetHandle
 * 
 * If you alter or omit these fields when performing a NET Modify, you will 
 * receive an error.
 * 
 * For information on the pocLinks field, see the POC Link Payload.
 */
class Net extends Payload
{
	public function __construct()
	{
		$this->name = 'net';
		$this->init();
	}

	protected function init()
	{
		$this->create(new Selection('version', [4, 6]));
		$this->create(new MultiLine('comment'));
		$this->create(new Element('registrationDate'), 'created');
		$this->create(new Element('orgHandle'), 'org');
		$this->create(new Element('handle'));
		$this->create(new ObjectGroup('netBlocks', 'NetBlock'), 'net');
		$this->create(new Element('customerHandle'), 'customer');
		$this->create(new Element('parentNetHandle'), 'parentNet');
		$this->create(new Element('netName'), 'name');
		$this->create(new NamedGroup('originASes', 'originAS'), 'ASN');
		$this->create(new ObjectGroup('pocLinks', 'PocLinkRef'), 'poc');
	}

	// this one is quite tricky to figure out
	public function isValid()
	{
		if ($this->fixedModify()) {
			return $this->validModify();
		}
		return $this->validAssign();
	}

	private function validAssign()
	{
		$net  = $this->get('net')->isValid();
		$name = $this->get('name')->isValid();
		$ref  = $this->get('parentNet')->isValid();

		$cust = $this->get('customer')->isValid();
		$org  = $this->get('org')->isValid();

		return $ref and $net and $name and ($cust xor $org);
	}

	private function fixedModify()
	{
		$fixed = $this->filter('version', 'registrationDate', 'handle', 
			'netBlocks', 'parentNetHandle');

		$fixed = array_reduce($fixed, function ($carry, $item) {
			return $carry and $item->isValid();
		}, true);

		$cust = $this->get('customer')->isValid();
		$org  = $this->get('org')->isValid();

		return $fixed and ($cust or $org);
	}

	private function validModify()
	{
		$mod = $this->filter('comment', 'netName', 'originASes', 'pocLinks');

		return array_reduce($mod, function ($carry, $item) {
			return $carry or $item->isValid();
		}, false);
	}
}
