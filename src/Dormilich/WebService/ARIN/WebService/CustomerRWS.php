<?php
// CustomerRWS.php

namespace Dormilich\WebService\ARIN\WebService;

use Dormilich\WebService\Adapter\ClientAdapter;
use Dormilich\WebService\ARIN\Payloads\Customer;

class CustomerRWS extends AbstractWebService
{
    /**
     * Create a webservice to request WHOIS data. 
     * 
     * @param ClientAdapter $client A connection adapter.
     * @param array $config Webservice config options 
     * @return self
     */
    public function __construct(ClientAdapter $client, array $config = array())
    {
        $this->client = $client;

        $this->setOptions($config);

        $base = $this->isProduction() ? parent::PRODUCTION_HOST : parent::SANDBOX_HOST;
        $this->client->setBaseUri($base);
    }

    public function create($parentNet, Customer $payload)
    {
        return $this->submit('POST', sprintf('net/%s/customer', $parentNet));
    }

    public function read($handle)
    {
        return $this->submit('GET', sprintf('customer/%s', $handle));
    }

    public function update(Customer $payload)
    {
        return $this->submit('PUT', sprintf('customer/%s', $handle), [], $payload);
    }

    public function delete($handle)
    {
        return $this->submit('DELETE', sprintf('customer/%s', $handle));
    }
}
