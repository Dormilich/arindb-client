<?php

namespace Dormilich\WebService\ARIN;


use Dormilich\WebService\Adapter\ClientAdapter;
use Dormilich\WebService\ARIN\XMLHandler;
use Dormilich\WebService\ARIN\Payloads\Payload;

abstract class WebServiceSetup
{
    const SANDBOX         = 'test';

    const PRODUCTION      = 'live';

    const SANDBOX_HOST    = 'https://reg.ote.arin.net/rest/';

    const PRODUCTION_HOST = 'https://reg.arin.net/rest/';

    private $config       = [];

    protected $result;

    protected $client;

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

        $base = $this->isProduction() ? self::PRODUCTION_HOST : self::SANDBOX_HOST;
        $this->client->setBaseUri($base);
    }

    /**
     * Set the config options.
     * 
     * @param type array $options 
     * @return type
     */
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

    /**
     * Pass the request data to the connection object.
     * 
     * @param string $method HTTP method.
     * @param string $path Request specific URL path.
     * @param array $query Any URL parameters.
     * @param Payload|NULL $body The payload object.
     * @return Payload The parsed response.
     */
    protected function submit($method, $path, array $options = array(), Payload $body = NULL)
    {
        if ($body) {
            $body = $body->toXML($this->config['encoding'], $this->config['strict'])->saveXML();
        }
        if (count($options)) {
            $path .= ';' . http_build_query($options, '', ';', \PHP_QUERY_RFC3986);
        }
        $path .= '?apikey=' . rawurlencode($this->config['password']);

        $xml = $this->client->request($method, $path, $body);

        return Payload::loadXML($xml);
    }    

    /**
     * Convert a value into its boolean equivalents "true" resp. "false".
     * 
     * @param mixed $value 
     * @return string Boolean string.
     */
    public function bool2string($value)
    {
        return filter_var($value, \FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
    }
}