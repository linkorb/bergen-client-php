<?php

namespace Bergen\Client;

class ClientOptions
{
    /**
     * Request Handler
     * @var callable
     * @see \GuzzleHttp\Client::__construct
     */
    const HANDLER = 'handler';

    /**
     * Set this to true to communicate with the API over HTTPS.
     */
    const SECURE_HTTP = 'secure';

    /**
     * The name and optional port number of the API host.
     */
    const API_HOST = 'api_host';
}
