<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Elements\Element;
use Dormilich\WebService\ARIN\Payloads\Payload;
use Dormilich\WebService\ARIN\Payloads\Net;
use Dormilich\WebService\ARIN\Payloads\NetBlock;
use Test\Payload_TestCase;

class NetRequestTest extends Payload_TestCase
{
    /**
     * @expectedException Dormilich\WebService\ARIN\Exceptions\RequestException
     */
    public function testCreateNetDirectlyFails()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $payload = new Net;
        $payload['netName']   = 'NETNAME';
        $payload['parentNet'] = 'PARENTNETHANDLE';

        $arin->create($payload);
    }

    public function testAssignNet()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $payload = new Net;
        $block = new NetBlock;

        $block['type']  = 'A';
        $block['start'] = '10.0.0.0';
        $block['length']  = 24;
        $payload['net'] = $block;

        $payload['netName']   = 'NETNAME';
        $payload['parentNet'] = 'PARENTNETHANDLE';
        $payload['customer']  = 'C12341234';

        $arin->create($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/PARENTNETHANDLE/reassign?apikey=', $client->url);
    }

    public function testAllocateNet()
    {
        $client = $this->getClient('net-request');
        $arin = new CommonRWS($client);

        $payload = new Net;
        $block = new NetBlock;

        $block['type']  = 'A';
        $block['start'] = '10.0.0.0';
        $block['length']  = 24;
        $payload['net'] = $block;

        $payload['netName']   = 'NETNAME';
        $payload['parentNet'] = 'PARENTNETHANDLE';
        $payload['org']       = 'ARIN';

        $request = $arin->create($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/PARENTNETHANDLE/reallocate?apikey=', $client->url);
        $this->assertEquals($request, Payload::loadXML($client->body));
    }

    public function testReadNet()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $net = $arin->read(new Net('NET-10-0-0-0-1'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-10-0-0-0-1?apikey=', $client->url);
    }

    public function testUpdateNet()
    {
        $client = $this->getClient('net-response');
        $arin = new CommonRWS($client);

        $payload = $arin->read(new Net('NET-10-0-0-0-1'));
        $payload['netName'] = 'Her on my knee';

        $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-10-0-0-0-1?apikey=', $client->url);
    }

    public function testDeleteNet()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Net('NET-10-0-0-0-1'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-10-0-0-0-1?apikey=', $client->url);
    }

    public function testReadLiveNet()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $arin->read(new Net('NET-10-0-0-0-1'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/net/NET-10-0-0-0-1?apikey=my-pass-word', $client->url);
    }
}
