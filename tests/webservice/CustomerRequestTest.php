<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Payloads\Customer;
use Test\Payload_TestCase;

class CustomerRequestTest extends Payload_TestCase
{
    public function testServiceDefaultsToTestDatabase()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());
    }

    public function testSetServiceToTestDatabase()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, ['environment' => 'test']);

        $this->assertFalse($arin->isProduction());
    }

    public function testSetServiceToProductionDatabase()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, ['environment' => 'live']);

        $this->assertTrue($arin->isProduction());
    }

    public function testCreateCustomer()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

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

        $arin->create($payload, 'PARENTNETHANDLE');

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/PARENTNETHANDLE/customer?apikey=', $client->url);
    }

    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\RequestException
     */
    public function testCreateCustomerWithoutNetHandleFails()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);
        $payload = new Customer;

        $customer = $arin->create($payload);
    }

    public function testReadCustomer()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $customer = $arin->read(new Customer('CUST'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/customer/CUST?apikey=', $client->url);
    }

    public function testUpdateCustomer()
    {
        $client = $this->getClient('customer');
        $arin = new CommonRWS($client);

        $payload = $arin->read(new Customer('CUST'));
        $payload['private'] = 'off';

        $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/customer/CUST?apikey=', $client->url);
    }

    public function testDeleteCustomer()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Customer('CUST'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/customer/CUST?apikey=', $client->url);
    }

    public function testReadLiveCustomer()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $arin->read(new Customer('CUST'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/customer/CUST?apikey=my-pass-word', $client->url);
    }
}
