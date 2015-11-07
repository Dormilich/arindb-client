<?php

use Dormilich\WebService\ARIN\Payloads\Error;
use Test\Payload_TestCase;

class ErrorTest extends Payload_TestCase
{
    public function testParseXML()
    {
        $payload = new Error;
        $payload->parse($this->loadXML('error'));

        $this->assertSame([
            'message' => 'MESSAGE',
            'code' => 'E_SCHEMA_VALIDATION',
            'components' => [[
                'name' => 'NAME',
                'message' => 'MESSAGE',
            ]],
            'additionalInfo' => [
                'MESSAGE'
            ],
        ], $payload->getValue());
    }
}
