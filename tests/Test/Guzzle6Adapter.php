<?php

namespace Test;

use Dormilich\WebService\Adapter\ClientAdapter;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Guzzle6Adapter implements ClientAdapter
{
    protected $client;

    protected $baseUri;

    /**
     * Create instance.
     * 
     * @param array $options Guzzle configuration options.
     * @return self
     */
    public function __construct(array $options)
    {
        $this->client = new Client($options);
    }

    /**
     * Set the Guzzle base URI.
     * 
     * @param string $uri Base URI to use.
     * @return void
     */
    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
    }

    /**
     * Send a request to the targeted API URI and return the response body.
     * 
     * @param string $method HTTP method.
     * @param string $path Request path.
     * @param array $headers Request headers.
     * @param string $body Request body.
     * @return string Response body.
     */
    public function request($method, $path, array $headers = NULL, $body = NULL)
    {
        $options = [
            'base_uri' => $this->baseUri,
            'headers'  => $headers,
        ];

        if (is_string($body)) {
            $options['body'] = $body;
        }

        return $this->client->request($method, $path, $options)->getBody();
    }
}
