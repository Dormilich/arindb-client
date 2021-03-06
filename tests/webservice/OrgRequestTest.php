<?php

use Dormilich\WebService\ARIN\CommonRWS;
use Dormilich\WebService\ARIN\Payloads\Org;
use Dormilich\WebService\ARIN\Payloads\PocLinkRef;
use Test\Payload_TestCase;

class OrgRequestTest extends Payload_TestCase
{
    public function testCreateOrgDirectly()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, ['strict' => false]);

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

        $arin->create($payload);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org?apikey=', $client->url);
    }

    public function testCreateOrgFromNet()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, ['strict' => false]);

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

        $arin->create($payload, 'NET-HANDLE');

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/net/NET-HANDLE/org?apikey=', $client->url);
    }

    public function testReadOrg()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->read(new Org('ARIN'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN?apikey=', $client->url);
    }

    public function testUpdateOrg()
    {
        $client = $this->getClient('org');
        $arin = new CommonRWS($client, ['strict' => false]);

        $payload = $arin->read(new Org('ARIN'));
        $payload['postalCode'] = 90210;

        $arin->update($payload);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN?apikey=', $client->url);
    }

    public function testDeleteOrg()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $arin->delete(new Org('ARIN'));

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN?apikey=', $client->url);
    }

    public function testAddPocLinkToOrg()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $org = new Org('ARIN');
        $ref = new PocLinkRef('MASTER-ARIN');
        $ref->set('function', 'AB');

        $arin->add($org, $ref);

        $this->assertSame('PUT', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN/poc/MASTER-ARIN;pocFunction=AB?apikey=', $client->url);
    }

    public function testDeletePocLinkFromOrg()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client);

        $org = new Org('ARIN');
        $ref = new PocLinkRef('MASTER-ARIN');
        $ref->set('function', 'AB');

        $arin->delete($org, $ref);

        $this->assertSame('DELETE', $client->method);
        $this->assertSame('https://reg.ote.arin.net/rest/org/ARIN/poc/MASTER-ARIN;pocFunction=AB?apikey=', $client->url);
    }

    public function testReadLiveOrg()
    {
        $client = $this->getClient();
        $arin = new CommonRWS($client, [
            'environment' => 'live',
            'password'    => 'my-pass-word',
        ]);

        $arin->read(new Org('ARIN'));

        $this->assertSame('GET', $client->method);
        $this->assertSame('https://reg.arin.net/rest/org/ARIN?apikey=my-pass-word', $client->url);
    }
}
