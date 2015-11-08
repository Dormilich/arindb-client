<?php
// AbstractWebService.php

namespace Dormilich\WebService\ARIN\WebService;

use Dormilich\WebService\Adapter\ClientAdapter;
use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\Payloads\Payload;

abstract class AbstractWebService
{
    const SANDBOX         = 'test';

    const PRODUCTION      = 'live';

    const SANDBOX_HOST    = 'https://reg.ote.arin.net/rest/';

    const PRODUCTION_HOST = 'https://reg.arin.net/rest/';

    private $config       = [];

    protected $results    = [];

    protected $client;

    /**
     * Create a webservice to request WHOIS data. 
     * 
     * @param ClientAdapter $client A connection adapter.
     * @param array $config Webservice config options 
     * @return self
     */
    public function init(ClientAdapter $client, array $config = array())
    {
        $this->client = $client;

        $this->setOptions($config);

        $base = $this->isProduction() ? self::PRODUCTION_HOST : self::SANDBOX_HOST;
        $this->client->setBaseUri($base);
    }

    protected function setOptions(array $options)
    {
        $defaults = [
            'environment' => self::SANDBOX,
            'password'    => '', 
            'strict'      => XMLHandler::VALIDATE,
            'encoding'    => 'UTF-8',
        ];
        $this->config = $options + $defaults;
    }

    /**
     * Whether the live database is used.
     * 
     * @return boolean
     */
    public function isProduction()
    {
        return strtolower($this->config['environment']) === self::PRODUCTION;
    }

    public function setEncoding($encoding)
    {
        $this->config['encoding'] = (string) $encoding;
    }

    protected function submit($method, $path, array $query = array(), Payload $body = NULL)
    {
        if ($body) {
            $body = $body->toXML($this->config['encoding'], $this->config['strict'])->saveXML();
        }
        $query = ['apikey' => $this->config['password']] + $query;
        $path .= '?' . http_build_query($query);

        $xml = $this->client->request($method, $path, $body);

        return Payload::loadXML($xml);
    }
}
