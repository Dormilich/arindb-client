<?php

use Dormilich\WebService\ARIN\Payloads\Roa;
use Test\Payload_TestCase;

class RoaTest extends Payload_TestCase
{
	const XMLNS = 'http://www.arin.net/regrws/rpki/v1';

	public function testValidity()
	{
		$payload = new Roa;

		$payload['data'] = '1|1340135296|My First ROA|1234|05-25-2011|05-25-2012|10.0.0.0|8|16|';

		$this->assertFalse($payload->isValid());

		$payload['signature'] = 'RGWqTw...sviQ==';

		$this->assertTrue($payload->isValid());
	}

	public function testSerialise()
	{
		$payload = new Roa;

		$payload['data'] = '1|1340135296|My First ROA|1234|05-25-2011|05-25-2012|10.0.0.0|8|16|';
		$payload['signature'] = 'RGWqTw...sviQ==';

		$actual = $payload->toXML(NULL);
		$expected = $this->loadDOM('roa');

		$this->assertSame($expected->saveXML(), $actual->saveXML());
	}

	public function testParseXML()
	{
		$payload = new Roa;
		$payload->parse($this->loadXML('roa'));

		$this->assertSame([
			'roaData' => '1|1340135296|My First ROA|1234|05-25-2011|05-25-2012|10.0.0.0|8|16|',
			'signature' => 'RGWqTw...sviQ==',
		], $payload->getValue());
	}
}
