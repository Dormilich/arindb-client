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
        $client = $this->getClient();
        $arin = new CommonRWS($client);

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

        $arin->create($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc;makeLink=false?apikey=', $client->url);

        $poc = $arin->create($payload, true);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc;makeLink=true?apikey=', $client->url);
    }

    public function testReadPoc()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->read(new Poc('ARIN-HOSTMASTER'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=', $client->url);
    }

    public function testUpdatePoc()
    {
        $client = $this->getClient('poc');
        $arin = new CommonRWS($client);

        $payload = $arin->read(new Poc('ARIN-HOSTMASTER'));
        $payload['company'] = 'ARIN';

        $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=', $client->url);
    }

    public function testDeletePoc()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Poc('ARIN-HOSTMASTER'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=', $client->url);
    }

    public function testReadLivePoc()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $arin->read(new Poc('ARIN-HOSTMASTER'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/poc/ARIN-HOSTMASTER?apikey=my-pass-word', $client->url);
    }
}
