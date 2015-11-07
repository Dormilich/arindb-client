<?php

use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Delegation;
use Dormilich\WebService\ARIN\Payloads\DelegationKey;
use Test\Payload_TestCase;

class DelegationTest extends Payload_TestCase
{
	public function testSerialise()
	{
		$payload = new Delegation;
		$key = new DelegationKey;

		$key['algorithm'] = '5';
		$key['digest'] = '0DC99D4B6549F83385214189CA48DC6B209ABB71';
		$key['ttl'] = '86400';
		$key['type'] = '1';
		$key['keyTag'] = '264';

		$payload['delegationKeys'][] = $key;

		$payload['nameservers'][0] = new Element('nameserver');
		$payload['nameservers'][0]->setValue('NS4.EXAMPLE.COM');
		$payload['nameservers'][1] = new Element('nameserver');
		$payload['nameservers'][1]->setValue('NS5.EXAMPLE.COM');

		$this->assertTrue($payload->isValid());

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('delegation-request');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

	public function testParseXML()
	{
		$payload = new Delegation;
		$payload->parse($this->loadXML('delegation-response'));

		$this->assertSame([
			'name' => '0.76.in-addr.arpa.', 
			'delegationKeys' => [[
				'algorithm'  => '5',
				'digest'     => '0DC99D4B6549F83385214189CA48DC6B209ABB71',
				'ttl'        => 86400,
				'digestType' => '1',
				'keyTag'     => '264',
			]],
			'nameservers' => [
				'NS4.EXAMPLE.COM',
				'NS5.EXAMPLE.COM',
			],
		], $payload->getValue());
		$this->assertSame('RSA/SHA-1', $payload['delegationKeys'][0]['algorithm']->name);
		$this->assertSame('SHA-1',     $payload['delegationKeys'][0]['digestType']->name);

		$this->assertFalse($payload->isValid());
	}
}
