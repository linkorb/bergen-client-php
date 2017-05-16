# linkorb/bergen-client-php

A client library for the [linkorb/bergen][] storage API.

## Install

    $ composer require --no-dev linkorb/bergen-client-php

## Example usage

    <?php

    require_once __DIR__ . '/vendor/autoload.php';

    use GuzzleHttp\RequestOptions;

    use Bergen\Client\ClientOptions;
    use Bergen\Client\ClientBuilder;
    use Bergen\Client\V1\V1StorageClient;

    $config = [
        RequestOptions::AUTH => ['myuser', 'mypass'],
        ClientOptions::API_HOST => 'localhost:8080',
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


[linkorb/bergen]: <https://github.com/linkorb/bergen> "linkorb/bergen at GitHub"
