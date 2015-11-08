<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Payloads\Customer;
use Test\Payload_TestCase;

class CustomerRequestTest extends Payload_TestCase
{
    public function testCreateCustomer()
    {
        $client = $this->getClient('customer');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = new Customer;

        $payload['name']    = 'CUSTOMERNAME';
        $payload['country']['name']  = 'UNITED STATES';
        $payload['country']['code2'] = 'US';
        $payload['country']['code3'] = 'USA';
        $payload['country']['e164']  = 1;
        $payload['handle']  = 'CUST';
        $payload['address'] = 'Line 1';
        $payload['city']    = 'Chantilly';
        $payload['state']   = 'VA';
        $payload['postalCode'] = '20151';
        $payload['comment'] = 'Line 1';
        $payload['org']     = 'PARENTORGHANDLE';
        $payload['created'] = 'Mon Nov 07 14:04:28 EST 2011';
        $payload['private'] = 'off';

        $customer = $arin->create($payload, 'PARENTNETHANDLE');

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/PARENTNETHANDLE/customer?apikey=', $client->url);
        $this->assertEquals($payload, $customer);
    }

    public function testReadCustomer()
    {
        $client = $this->getClient('customer');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        #$customer = $arin->read('CUST');
        $customer = $arin->get(new Customer('CUST'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/customer/CUST?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Customer', $customer);
    }

    public function testUpdateCustomer()
    {
        $client = $this->getClient('customer');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        #$payload = $arin->read('CUST');
        $payload = $arin->get(new Customer('CUST'));

        // edit properties here

        $customer = $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/customer/CUST?apikey=', $client->url);
        $this->assertEquals($payload, $customer);
    }

    public function testDeleteCustomer()
    {
        $client = $this->getClient('customer');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $customer = $arin->delete(new Customer('CUST'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/customer/CUST?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Customer', $customer);
    }

    public function testReadLiveCustomer()
    {
        $client = $this->getClient('customer');
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $this->assertTrue($arin->isProduction());

        $customer = $arin->get(new Customer('CUST'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/customer/CUST?apikey=my-pass-word', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Customer', $customer);
    }
}
