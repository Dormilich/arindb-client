<?php

use Dormilich\WebService\ARIN\Payloads\DelegationKey;
use Test\Payload_TestCase;

class DelegationKeyTest extends Payload_TestCase
{
	public function testDigestProperty()
	{
		$payload = new DelegationKey;
		$hash = md5('The quick brown fox jumped over the lazy dog.');

		$this->assertFalse($payload['digest']->isValid());
		$this->assertNull($payload['digest']->getValue());

		$payload['digest'] = $hash;

		$this->assertTrue($payload['digest']->isValid());
		$this->assertSame($hash, $payload['digest']->getValue());

		unset($payload['digest']);
		$this->assertFalse($payload['digest']->isValid());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testDigestRejectsNonHashValue()
	{
		$payload = new DelegationKey;
		$payload['digest'] = 'The quick brown fox...';
	}

	public function testKeyTagProperty()
	{
		$payload = new DelegationKey;

		$this->assertFalse($payload['keyTag']->isValid());
		$this->assertNull($payload['keyTag']->getValue());

		$payload['keyTag'] = 12345;

		$this->assertTrue($payload['keyTag']->isValid());
		$this->assertSame('12345', $payload['keyTag']->getValue());

		unset($payload['keyTag']);
		$this->assertFalse($payload['keyTag']->isValid());
	}

	public function testTtlProperty()
	{
		$payload = new DelegationKey;

		$this->assertFalse($payload['ttl']->isValid());
		$this->assertNull($payload['ttl']->getValue());

		$payload['ttl'] = 3600;

		$this->assertTrue($payload['ttl']->isValid());
		$this->assertSame(3600, $payload['ttl']->getValue());
		$this->assertSame('3600', (string) $payload['ttl']);

		unset($payload['ttl']);
		$this->assertFalse($payload['ttl']->isValid());

		// lower boundary
		$payload['ttl'] = 3600;
	}

	public function invalidTTLValueProvider()
	{
		return [
			[0], ['states'], [-18], [''], [3.14], 
		];
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 * @dataProvider invalidTTLValueProvider
	 */
	public function testTtlSetInvalidValueFails($value)
	{
		$payload = new DelegationKey;
		$payload['ttl'] = $value;
	}

	public function testAlgorithmProperty()
	{
		$payload = new DelegationKey;

		$this->assertFalse($payload['algorithm']->isValid());
		$this->assertNull($payload['algorithm']->getValue());

		$payload['algorithm'] = 8;

		$this->assertTrue($payload['algorithm']->isValid());
		$this->assertSame('8', $payload['algorithm']->getValue());

		unset($payload['algorithm']);
		$this->assertFalse($payload['algorithm']->isValid());

		// other valid values

		$payload['algorithm'] = 7;
		$payload['algorithm'] = 5;

		// attribute

		$payload['algorithm']->name = 'MD5';
		$this->assertSame('MD5', $payload['algorithm']->name);
	}

	public function invalidAlgoValueProvider()
	{
		return [
			[0], [1], [2], [3], [4], [6], [9], [10], ['foo'], [false], 
		];
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 * @dataProvider invalidAlgoValueProvider
	 */
	public function testAlgoSetInvalidValueFails($value)
	{
		$payload = new DelegationKey;
		$payload['algorithm'] = $value;
	}

	public function testDigestTypeProperty()
	{
		$payload = new DelegationKey;

		$this->assertFalse($payload['type']->isValid());
		$this->assertNull($payload['type']->getValue());

		$payload['type'] = 1;

		$this->assertTrue($payload['type']->isValid());
		$this->assertSame('1', $payload['type']->getValue());

		unset($payload['type']);
		$this->assertFalse($payload['type']->isValid());

		// using name instead of alias

		$payload['digestType'] = 2;

		$this->assertSame('2', $payload['type']->getValue());
		$this->assertSame('2', $payload['digestType']->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testDigestTypeSetInvalidValueFails()
	{
		$payload = new DelegationKey;
		$payload['algorithm'] = 3;
	}

	public function testGetPayloadAsArray()
	{
		$payload = new DelegationKey;

		$payload['algorithm'] = 5;
		$payload['digest'] = '0DC99D4B6549F83385214189CA48DC6B209ABB71';
		$payload['type'] = 1;

		$this->assertSame([
			'algorithm'  => '5', 
			'digest'     => '0DC99D4B6549F83385214189CA48DC6B209ABB71', 
			'digestType' => '1', 
		], $payload->getValue(true));
		$this->assertSame([
			'algorithm'  => '5', 
			'digest'     => '0DC99D4B6549F83385214189CA48DC6B209ABB71', 
			'ttl'        => NULL, 
			'digestType' => '1', 
			'keyTag'     => NULL, 
		], $payload->getValue());

		$payload['ttl'] = 1440;
		$payload['keyTag'] = 365;

		$this->assertSame([
			'algorithm'  => '5', 
			'digest'     => '0DC99D4B6549F83385214189CA48DC6B209ABB71', 
			'ttl'        => 1440, 
			'digestType' => '1', 
			'keyTag'     => '365', 
		], $payload->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
	 */
	public function testSetUnknownPropertyFails()
	{
		$payload = new DelegationKey;
		$payload['issuer'] = 'John Doe';
	}

	public function testValidity()
	{
		$payload = new DelegationKey;

		$this->assertFalse($payload->isValid());

		$payload['algorithm'] = 5;
		$payload['digest'] = '0DC99D4B6549F83385214189CA48DC6B209ABB71';
		$this->assertFalse($payload->isValid());

		$payload['type'] = 1;
		$payload['ttl'] = 1440;
		$payload['keyTag'] = 365;

		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new DelegationKey;

		$payload['algorithm'] = 5;
		$payload['digest']    = '0DC99D4B6549F83385214189CA48DC6B209ABB71';
		$payload['type']      = 1;
		$payload['ttl']       = 86400;
		$payload['keyTag']    = 264;

		$payload['algorithm']->name = 'RSA/SHA-1';
		$payload['type']->name = 'SHA-1';

		$this->assertTrue($payload->isValid());

		$doc = new DOMDocument;
		$node = $payload->toDOM($doc);
		$actual = $doc->saveXML($node);

		$xml = $this->loadDOM('delegation-key');
		$expected = $xml->saveXML($xml->documentElement);

		$this->assertSame($expected, $actual);
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage This Delegation Key Payload should not be submitted by itself.
	 */
	public function testSerialiseAsRequestPayloadFails()
	{
		$payload = new DelegationKey;
		 $payload['digest'] = '0DC99D4B6549F83385214189CA48DC6B209ABB71';

		$xml = $payload->toXML();
	}

	public function testParseXML()
	{
		$payload = new DelegationKey;
		$payload->parse($this->loadXML('delegation-key'));

		$this->assertSame([
			'algorithm'  => '5', 
			'digest'     => '0DC99D4B6549F83385214189CA48DC6B209ABB71', 
			'ttl'        => 86400, 
			'digestType' => '1', 
			'keyTag'     => '264', 
		], $payload->getValue());
	}
}
