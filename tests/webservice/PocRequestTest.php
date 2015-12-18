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

    public function testPocAddPhone()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $phone = new Phone;
        $phone['number'] = '+1.234.5678';
        $phone['type']['code'] = 'M';

        $arin->add(new Poc('POCHANDLE'), $phone);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/phone?apikey=', $client->url);
    }

    public function testPocDeletePhone()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $phone = new Phone;
        $phone['number'] = '+1.234.5678';
        $phone['type']['code'] = 'M';

        $arin->delete(new Poc('POCHANDLE'), $phone);

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/phone/+1.234.5678;type=M?apikey=', $client->url);

        $arin->delete(new Poc('POCHANDLE'), $phone['type']);

        $this->assertSame('DELETE', $client->method);
        // that one is an educated guess, as this specific case is not demonstrated
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/phone/;type=M?apikey=', $client->url);

        $arin->delete(new Poc('POCHANDLE'), 'M');

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/phone/;type=M?apikey=', $client->url);

        $arin->delete(new Poc('POCHANDLE'), '+1.234.5678');

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/phone/+1.234.5678?apikey=', $client->url);
    }

    public function testPocDeletePhoneReadsInvalidInputAsPhoneNumber()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Poc('POCHANDLE'), 'fizz-buzz');

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/phone/fizz-buzz?apikey=', $client->url);
    }

    public function testPocAddEmail()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->add(new Poc('POCHANDLE'), 'arin@example.com');

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/email/arin@example.com?apikey=', $client->url);
    }

    public function testPocDeleteEmail()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Poc('POCHANDLE'), 'arin@example.com');

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/poc/POCHANDLE/email/arin@example.com?apikey=', $client->url);
    }
}
