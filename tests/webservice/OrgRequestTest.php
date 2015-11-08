<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Payloads\Org;
use Test\Payload_TestCase;

class OrgRequestTest extends Payload_TestCase
{
    public function testCreateOrgDirectly()
    {
        $client = $this->getClient('org');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = new Org;

        $payload['country']['name']  = 'UNITED STATES';
        $payload['country']['code2'] = 'US';
        $payload['country']['code3'] = 'USA';
        $payload['country']['e164']  = '1';
        $payload['address'] = 'Line 1';
        $payload['city'] = 'Chantilly';
        $payload['state'] = 'VA';
        $payload['postalCode'] = '20151';
        $payload['comment'] = 'Line 1';
        $payload['created'] = 'Mon Nov 07 14:04:28 EST 2011';
        $payload['handle'] = 'ARIN';
        $payload['orgName'] = 'ORGNAME';
        $payload['dbaName'] = 'DBANAME';
        $payload['taxId'] = 'TAXID';
        $payload['orgUrl'] = 'ORGURL';

        $org = $arin->create($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org?apikey=', $client->url);
        $this->assertEquals($payload, $org);
    }

    public function testCreateOrgFromNet()
    {
        $client = $this->getClient('org');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = new Org;

        $payload['country']['name']  = 'UNITED STATES';
        $payload['country']['code2'] = 'US';
        $payload['country']['code3'] = 'USA';
        $payload['country']['e164']  = '1';
        $payload['address'] = 'Line 1';
        $payload['city'] = 'Chantilly';
        $payload['state'] = 'VA';
        $payload['postalCode'] = '20151';
        $payload['comment'] = 'Line 1';
        $payload['created'] = 'Mon Nov 07 14:04:28 EST 2011';
        $payload['handle'] = 'ARIN';
        $payload['orgName'] = 'ORGNAME';
        $payload['dbaName'] = 'DBANAME';
        $payload['taxId'] = 'TAXID';
        $payload['orgUrl'] = 'ORGURL';

        $org = $arin->create($payload, 'NET-HANDLE');

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-HANDLE/org?apikey=', $client->url);
        $this->assertEquals($payload, $org);
    }

    public function testReadOrg()
    {
        $client = $this->getClient('org');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $org = $arin->read(new Org('ARIN'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Org', $org);
    }

    public function testUpdateOrg()
    {
        $client = $this->getClient('org');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $payload = $arin->read(new Org('ARIN'));

        // edit properties here

        $org = $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN?apikey=', $client->url);
        $this->assertEquals($payload, $org);
    }

    public function testDeleteOrg()
    {
        $client = $this->getClient('org');
        $arin = new CommonRWS($client);

        $this->assertFalse($arin->isProduction());

        $org = $arin->delete(new Org('ARIN'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN?apikey=', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Org', $org);
    }

    public function testReadLiveOrg()
    {
        $client = $this->getClient('org');
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $this->assertTrue($arin->isProduction());

        $org = $arin->read(new Org('ARIN'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/org/ARIN?apikey=my-pass-word', $client->url);
        $this->assertInstanceOf('Dormilich\WebService\ARIN\Payloads\Org', $org);
    }
}
