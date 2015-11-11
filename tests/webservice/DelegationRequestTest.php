<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Delegation;
use Test\Payload_TestCase;

class DelegationRequestTest extends Payload_TestCase
{
    public function testReadDelegation()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->read(new Delegation('ARIN'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/delegation/ARIN?apikey=', $client->url);
    }

    public function testUpdateDelegation()
    {
        $client = $this->getClient('delegation-response');
        $arin = new CommonRWS($client);

        $payload = $arin->read(new Delegation('0.76.in-addr.arpa.'));
        $payload['nameservers'][] = Element::createWith('nameserver', 'NS6.EXAMPLE.COM');

        $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/delegation/0.76.in-addr.arpa.?apikey=', $client->url);
    }

    public function testAddNameserver()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->add(new Delegation('ARIN'), 'NS1.EXAMPLE.COM');

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/delegation/ARIN/nameserver/NS1.EXAMPLE.COM?apikey=', $client->url);
    }

    public function testDeleteNameserver()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Delegation('ARIN'), 'NS1.EXAMPLE.COM');

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/delegation/ARIN/nameserver/NS1.EXAMPLE.COM?apikey=', $client->url);
    }

    public function testDeleteAllNameservers()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Delegation('ARIN'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/delegation/ARIN/nameservers?apikey=', $client->url);
    }

    public function testReadLiveDelegation()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $delegation = $arin->read(new Delegation('ARIN'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/delegation/ARIN?apikey=my-pass-word', $client->url);
    }
}
