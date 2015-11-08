<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Phone;
use Dormilich\WebService\ARIN\Payloads\Poc;
use Test\Payload_TestCase;

class PocRequestTest extends Payload_TestCase
{
    public function testCreatePoc()
    {
        $client = $this->getClient('poc');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = new Poc;
        $phone = new Phone;

        $payload['country']['name']  = 'UNITED STATES';
        $payload['country']['code2'] = 'US';
        $payload['country']['code3'] = 'USA';
        $payload['country']['e164']  = '1';
        $payload['address']    = 'Line 1';
        $payload['city']       = 'Chantilly';
        $payload['state']      = 'VA';
        $payload['postalCode'] = '20151';
        $payload['emails']     = Element::createWith('email', 'you@example.com');
        $payload['comment']    = 'Line 1';
        $payload['created']    = 'Mon Nov 07 14:04:28 EST 2011';
        $payload['handle']     = 'ARIN-HOSTMASTER';
        $payload['type']       = 'PERSON';
        $payload['company']    = 'COMPANYNAME';
        $payload['firstName']  = 'FIRSTNAME';
        $payload['middleName'] = 'MIDDLENAME';
        $payload['lastName']   = 'LASTNAME';

        $phone['number'] = '+1.703.227.9840';
        $phone['extension'] = '101';
        $phone['type']['description'] = 'DESCRIPTION';
        $phone['type']['code'] = 'O';
        $payload['phones'] = $phone;

        $poc = $arin->create($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc;makeLink=false?apikey=', $client->url);
        $this->assertEquals($payload, $poc);

        $poc = $arin->create($payload, true);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc;makeLink=true?apikey=', $client->url);
    }

    public function testReadPoc()
    {
        $client = $this->getClient('poc');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $poc = $arin->read(new Poc('ARIN-HOSTMASTER'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Poc', $poc);
    }

    public function testUpdatePoc()
    {
        $client = $this->getClient('poc');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = $arin->read(new Poc('ARIN-HOSTMASTER'));

        // edit properties here

        $poc = $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=', $client->url);
        $this->assertEquals($payload, $poc);
    }

    public function testDeletePoc()
    {
        $client = $this->getClient('poc');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $poc = $arin->delete(new Poc('ARIN-HOSTMASTER'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Poc', $poc);
    }

    public function testReadLivePoc()
    {
        $client = $this->getClient('poc');
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $this->assertTrue($arin->isProduction());

        $poc = $arin->read(new Poc('ARIN-HOSTMASTER'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=my-pass-word', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Poc', $poc);
    }
}
