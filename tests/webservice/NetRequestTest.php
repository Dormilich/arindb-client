<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Elements\Element;
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
        $client = $this->getClient(NULL);
        $arin = new CommonRWS($client);

        $payload = new Net;
        $payload['netName']   = 'NETNAME';
        $payload['parentNet'] = 'PARENTNETHANDLE';

        $net = $arin->create($payload);
    }

    public function testAssignNet()
    {
        $client = $this->getClient('tr-net');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = new Net;
        $block = new NetBlock;

        $block['type']  = 'A';
        $block['start'] = '10.0.0.0';
        $block['cidr']  = 24;
        $payload['net'] = $block;

        $payload['netName']   = 'NETNAME';
        $payload['parentNet'] = 'PARENTNETHANDLE';
        $payload['customer']  = 'C12341234';

        $tr = $arin->assign($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/PARENTNETHANDLE/reassign?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $tr['net']);
    }

    public function testAllocateNet()
    {
        $client = $this->getClient('tr-net');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = new Net;
        $block = new NetBlock;

        $block['type']  = 'A';
        $block['start'] = '10.0.0.0';
        $block['cidr']  = 24;
        $payload['net'] = $block;

        $payload['netName']   = 'NETNAME';
        $payload['parentNet'] = 'PARENTNETHANDLE';
        $payload['org']       = 'ARIN';

        $tr = $arin->allocate($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/PARENTNETHANDLE/reallocate?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $tr['net']);
    }

    public function testReadNet()
    {
        $client = $this->getClient('net-response');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $net = $arin->read(new Net('NET-10-0-0-0-1'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-10-0-0-0-1?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $net);
    }

    public function testUpdateNet()
    {
        $client = $this->getClient('net-response');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = $arin->read(new Net('NET-10-0-0-0-1'));

        // edit properties here

        $net = $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-10-0-0-0-1?apikey=', $client->url);
        $this->assertEquals($payload, $net);
    }

    public function testDeleteNet()
    {
        $client = $this->getClient('tr-net');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $tr = $arin->delete(new Net('NET-10-0-0-0-1'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-10-0-0-0-1?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $tr['net']);
    }

    public function testReadLiveNet()
    {
        $client = $this->getClient('net-response');
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $this->assertTrue($arin->isProduction());

        $net = $arin->read(new Net('NET-10-0-0-0-1'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/net/NET-10-0-0-0-1?apikey=my-pass-word', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Net', $net);
    }
}
