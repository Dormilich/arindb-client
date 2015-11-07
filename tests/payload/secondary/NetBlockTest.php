<?php

use Dormilich\WebService\ARIN\Elements\IP;
use Dormilich\WebService\ARIN\Payloads\NetBlock;
use Test\Payload_TestCase;

class NetBlockTest extends Payload_TestCase
{
	public function testTypeProperty()
	{
		$payload = new NetBlock;

		$this->assertFalse($payload['type']->isValid());
		$this->assertNull($payload['type']->getValue());

		$payload['type'] = 'S';

		$this->assertTrue($payload['type']->isValid());
		$this->assertSame('S', $payload['type']->getValue());

		unset($payload['type']);
		$this->assertFalse($payload['type']->isValid());
	}

	public function validTypeValueProvider()
	{
		return [
			['A'],  ['AF'], ['AP'], ['AR'], ['AV'], ['DA'], ['DS'], ['FX'], ['IR'], ['IU'], 
			['LN'], ['LX'], ['PV'], ['PX'], ['RD'], ['RN'], ['RV'], ['RX'], ['S'], 
		];
	}

	/**
	 * @dataProvider validTypeValueProvider
	 */
	public function testTypeWithValidInput($value)
	{
		$payload = new NetBlock;
		$payload['type'] = $value;

		$this->assertSame($value, (string) $payload['type']);
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testTypeWithInvalidInput()
	{
		$payload = new NetBlock;
		$payload['type'] = 'AX';
	}

	public function testDescriptionProperty()
	{
		$payload = new NetBlock;

		$this->assertFalse($payload['description']->isValid());
		$this->assertNull($payload['description']->getValue());

		$payload['description'] = 'my subnet';

		$this->assertTrue($payload['description']->isValid());
		$this->assertSame('my subnet', $payload['description']->getValue());

		unset($payload['description']);
		$this->assertFalse($payload['description']->isValid());
	}

	public function testStartAddressProperty()
	{
		$payload = new NetBlock;

		$this->assertFalse($payload['start']->isValid());
		$this->assertNull($payload['start']->getValue());

		$payload['start'] = '127.000.000.001';

		// default: unpadded
		$this->assertTrue($payload['start']->isValid());
		$this->assertSame('127.0.0.1', $payload['start']->getValue());

		// overwrite default
		$payload['start']->setValue('127.000.000.001', IP::PADDED);
		$this->assertSame('127.000.000.001', $payload['start']->getValue());

		// apply padding
		$payload['start']->setValue('127.0.0.1', IP::PADDED);
		$this->assertSame('127.000.000.001', $payload['start']->getValue());

		unset($payload['start']);
		$this->assertFalse($payload['start']->isValid());

		// using name instead of alias

		$payload['startAddress'] = '192.168.2.1';

		$this->assertSame('192.168.2.1', $payload['startAddress']->getValue());
		$this->assertSame('192.168.2.1', $payload['start']->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testStartAddressWithInvalidInput()
	{
		$payload = new NetBlock;
		$payload['start'] = 'foobar';
	}

	public function testEndAddressProperty()
	{
		$payload = new NetBlock;

		$this->assertFalse($payload['end']->isValid());
		$this->assertNull($payload['end']->getValue());

		$payload['end'] = '127.000.000.001';

		// default: unpadded
		$this->assertTrue($payload['end']->isValid());
		$this->assertSame('127.0.0.1', $payload['end']->getValue());

		// overwrite default
		$payload['end']->setValue('127.000.000.001', IP::PADDED);
		$this->assertSame('127.000.000.001', $payload['end']->getValue());

		// apply padding
		$payload['end']->setValue('127.0.0.1', IP::PADDED);
		$this->assertSame('127.000.000.001', $payload['end']->getValue());

		unset($payload['end']);
		$this->assertFalse($payload['end']->isValid());

		// using name instead of alias

		$payload['endAddress'] = '192.168.2.1';

		$this->assertSame('192.168.2.1', $payload['endAddress']->getValue());
		$this->assertSame('192.168.2.1', $payload['end']->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 */
	public function testEndAddressWithInvalidInput()
	{
		$payload = new NetBlock;
		$payload['end'] = -20;
	}

	public function testCIDRProperty()
	{
		$payload = new NetBlock;

		$this->assertFalse($payload['cidr']->isValid());
		$this->assertNull($payload['cidr']->getValue());

		$payload['cidr'] = 29;

		$this->assertTrue($payload['cidr']->isValid());
		$this->assertSame(29, $payload['cidr']->getValue());

		unset($payload['cidr']);
		$this->assertFalse($payload['cidr']->isValid());

		// using name instead of alias

		$payload['cidrLength'] = 18;

		$this->assertSame(18, $payload['cidrLength']->getValue());
		$this->assertSame(18, $payload['cidr']->getValue());

		// boundaries

		$payload['cidr'] = 0;
		$payload['cidr'] = 128;
	}

	public function invalidPrefixProvider()
	{
		return [
			['foo'], [-1], [129], 
		];
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
	 * @dataProvider invalidPrefixProvider
	 */
	public function testCidrLengthWithInvalidInput($value)
	{
		$payload = new NetBlock;
		$payload['cidr'] = $value;
	}

	public function testGetPayloadAsArray()
	{
		$payload = new NetBlock;

		$payload['start'] = '192.168.2.0';
		$payload['end']   = '192.168.2.31';

		$this->assertSame([
			'type' => NULL, 
			'description'  => NULL, 
			'startAddress' => '192.168.2.0', 
			'endAddress'   => '192.168.2.31', 
			'cidrLength'   => NULL, 
		], $payload->getValue());

		$payload['description'] = 'subnet';
		$payload['type'] = 'FX';
		$payload['cidr'] = 27;

		$this->assertSame([
			'type' => 'FX', 
			'description'  => 'subnet', 
			'startAddress' => '192.168.2.0', 
			'endAddress'   => '192.168.2.31', 
			'cidrLength'   => 27, 
		], $payload->getValue());
	}

	/**
	 * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
	 */
	public function testSetUnknownPropertyFails()
	{
		$payload = new NetBlock;
		$payload['netname'] = 'foo';
	}

	public function testValidity()
	{
		$payload = new NetBlock;

		$this->assertFalse($payload->isValid());

		$payload['type'] = 'FX';
		$payload['description'] = 'subnet';
		$payload['start'] = '192.168.2.0';
		$this->assertFalse($payload->isValid());

		// minimum requirement
		$payload['type'] = 'FX';
		unset($payload['description']);
		$payload['start'] = '192.168.2.0';
		$payload['end']   = '192.168.2.31';
		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new NetBlock;

		$payload['type'] = 'A';
		$payload['description'] = 'DESCRIPTION';
		$payload['start'] = '10.0.0.0';
		$payload['cidr'] = 24;

		$this->assertTrue($payload->isValid());

		$doc = new DOMDocument;
		$node = $payload->toDOM($doc);
		$actual = $doc->saveXML($node);

		$xml = $this->loadDOM('net-block-request');
		$expected = $xml->saveXML($xml->documentElement);

		$this->assertSame($expected, $actual);
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage This Net Block Payload should not be submitted by itself.
	 */
	public function testSerialiseAsRequestPayloadFails()
	{
		$payload = new NetBlock;
		$payload['type'] = 'AV';

		$xml = $payload->toXML();
	}

	public function testParseXML()
	{
		$payload = new NetBlock;
		$payload->parse($this->loadXML('net-block-response'));

		$this->assertSame([
			'type' => 'A', 
			'description'  => 'DESCRIPTION', 
			'startAddress' => '10.0.0.0', 
			'endAddress'   => '10.0.0.255', 
			'cidrLength'   => 24, 
		], $payload->getValue());
	}
}
