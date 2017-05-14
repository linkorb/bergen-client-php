<?php

namespace Bergen\Client;

use GuzzleHttp\Client;

class ClientBuilder
{
    protected $config = array();
    protected $base_uri = '';

    /**
     * ClientBuilder is constructed with an array of configuration options.
     * For example:
     *     $builder = new ClientBuilder(array(
     *         'api_host' => 'some-hostname',
     *         'auth' => array('myusername', 's3kr3t')
     *     ));
     *
     * @see \Bergen\Client\ClientOptions  and
     * @see \GuzzleHttp\RequestOptions    for the options available.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->base_uri = $this->buildBaseUri();
    }

    /**
     * Build an API client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function build()
    {
        return new Client(
            array_merge($this->config, ['base_uri' => $this->base_uri])
        );
    }

    /**
     * Get the configured base URI.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->base_uri;
    }

    /**
     * Configure the builder with a base URI.
     *
     * @param string $base_uri
     */
    public function setBaseUri($base_uri)
    {
        $this->base_uri = $base_uri;

        return $this;
    }

    private function buildBaseUri()
    {
        $base_uri = 'http';
        if (isset($this->config[ClientOptions::SECURE_HTTP])
            && $this->config[ClientOptions::SECURE_HTTP] !== false
        ) {
            $base_uri .= 's';
        }
        $base_uri .= '://';

        if (isset($this->config[ClientOptions::API_HOST])) {
            $base_uri .= $this->config[ClientOptions::API_HOST];
        } else {
            $base_uri .= 'localhost';
        }

        return $base_uri . '/';
    }
}
