<?php

use Dormilich\WebService\ARIN\Payloads\Country;
use Test\Payload_TestCase;

class CountryTest extends Payload_TestCase
{
    public function testNameProperty()
    {
        $payload = new Country;

        $this->assertFalse($payload['name']->isDefined());
        $this->assertNull($payload['name']->getValue());

        $payload['name'] = 'Germany';

        $this->assertTrue($payload['name']->isDefined());
        $this->assertSame('Germany', $payload['name']->getValue());

        unset($payload['name']);
        $this->assertFalse($payload['name']->isDefined());
    }

    public function testCode2Property()
    {
        $payload = new Country;

        $this->assertFalse($payload['code2']->isDefined());
        $this->assertNull($payload['code2']->getValue());

        $payload['code2'] = 'US';

        $this->assertTrue($payload['code2']->isDefined());
        $this->assertSame('US', $payload['code2']->getValue());

        unset($payload['code2']);
        $this->assertFalse($payload['code2']->isDefined());
    }

    public function invalideCodeValueProvider()
    {
        return [
            [1], ['states'], ['U'], 
        ];
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
     * @dataProvider invalideCodeValueProvider
     */
    public function testCode2SetInvalidValueFails($value)
    {
        $payload = new Country;
        $payload['code2'] = $value;
    }

    public function testCode3Property()
    {
        $payload = new Country;

        $this->assertFalse($payload['code3']->isDefined());
        $this->assertNull($payload['code3']->getValue());

        $payload['code3'] = 'USA';

        $this->assertTrue($payload['code3']->isDefined());
        $this->assertSame('USA', $payload['code3']->getValue());

        unset($payload['code3']);
        $this->assertFalse($payload['code3']->isDefined());
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
     * @dataProvider invalideCodeValueProvider
     */
    public function testCode3SetInvalidValueFails($value)
    {
        $payload = new Country;
        $payload['code3'] = $value;
    }

    public function testE164Property()
    {
        $payload = new Country;

        $this->assertFalse($payload['e164']->isDefined());
        $this->assertNull($payload['e164']->getValue());

        $payload['e164'] = '42';

        $this->assertTrue($payload['e164']->isDefined());
        $this->assertSame(42, $payload['e164']->getValue());
        $this->assertSame('42', (string) $payload['e164']);

        unset($payload['e164']);
        $this->assertFalse($payload['e164']->isDefined());

        // lower limit
        $payload['e164'] = 1;
        // upper limit
        $payload['e164'] = 999;
    }

    public function invalidePhoneCodeProvider()
    {
        return [
            [0], ['states'], [-8], [123456], [3.14], [1000]
        ];
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\ConstraintException
     * @dataProvider invalidePhoneCodeProvider
     */
    public function testPhoneCodeSetInvalidValueFails($value)
    {
        $payload = new Country;
        $payload['e164'] = $value;
    }

    public function testGetPayloadAsArray()
    {
        $payload = new Country;

        $payload['name'] = 'UNITED STATES OF AMERICA';
        $payload['code2'] = 'US';

        $this->assertSame([
            'name'  => 'UNITED STATES OF AMERICA', 
            'code2' => 'US', 
        ], $payload->getValue(true));
        $this->assertSame([
            'name'  => 'UNITED STATES OF AMERICA', 
            'code2' => 'US', 
            'code3' => NULL, 
            'e164'  => NULL,
        ], $payload->getValue());

        $payload['code3'] = 'USA';
        $payload['e164'] = 1;

        $this->assertSame([
            'name'  => 'UNITED STATES OF AMERICA', 
            'code2' => 'US', 
            'code3' => 'USA', 
            'e164'  => 1,
        ], $payload->getValue());
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\NotFoundException
     */
    public function testSetUnknownPropertyFails()
    {
        $payload = new Country;
        $payload['president'] = 'Kennedy';
    }

    public function testValidity()
    {
        $payload = new Country;

        $this->assertFalse($payload->isDefined());
        $this->assertFalse($payload->isValid());

        $payload['name'] = 'GERMANY';
        $this->assertFalse($payload->isValid());

        $payload['code2'] = 'DE';
        $this->assertTrue($payload->isValid());
    }

    public function testSerialise()
    {
        $payload = new Country;

        $payload['name']  = 'UNITED STATES';
        $payload['code2'] = 'US';
        $payload['code3'] = 'USA';
        $payload['e164']  = 1;

        $this->assertTrue($payload->isValid());

        $doc = new DOMDocument;
        $node = $payload->toDOM($doc);
        $actual = $doc->saveXML($node);

        $xml = $this->loadDOM('country');
        $expected = $xml->saveXML($xml->documentElement);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage This Country Payload should not be submitted by itself.
     */
    public function testSerialiseAsRequestPayloadFails()
    {
        $payload = new Country;
        $payload['code2'] = 'UK';

        $xml = $payload->toXML();
    }

    public function testParseXML()
    {
        $payload = new Country;
        $payload->parse($this->loadXML('country'));

        $this->assertSame([
            'name'  => 'UNITED STATES', 
            'code2' => 'US', 
            'code3' => 'USA', 
            'e164'  => 1,
        ], $payload->getValue());
    }
}
