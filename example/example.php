<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\RequestOptions;

use Bergen\Client\ClientOptions;
use Bergen\Client\ClientBuilder;
use Bergen\Client\V1\V1StorageClient;

if (!getenv('BERGEN_USERNAME') || !getenv('BERGEN_PASSWORD') || !getenv('BERGEN_API_HOST')) {
    throw new Exception("Environment variables not set up correctly");
}

$config = [
    RequestOptions::AUTH => [getenv('BERGEN_USERNAME'), getenv('BERGEN_PASSWORD')],
    ClientOptions::API_HOST => getenv('BERGEN_API_HOST'),
    ClientOptions::SECURE_HTTP => false,
];
$builder = new ClientBuilder($config);
$client = new V1StorageClient($builder);

// The client library encodes the key with URL encoding, but I need to
// double-encode the key because it contains a forward-slash.
$key = rawurlencode('my/key');

// store the value
$client->put($key, 'a value to put into storage');

// get the value
$response = $client->get($key);
var_dump($response->getStatusCode());
var_dump($response->getHeaders());
var_dump((string) $response->getBody());

// delete the value
$client->delete($key);
