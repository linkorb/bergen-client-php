<?php

namespace Bergen\Client\V1;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;

use Bergen\Client\ClientBuilder;
use Bergen\Client\Error\ClientError;
use Bergen\Client\Error\ServerError;
use Bergen\Client\Error\UnexpectedResponseError;

/**
 * Client of the V1 Bergen storage API.
 */
class V1StorageClient
{
    const STORAGE_ENDPOINT_PATH = 'api/v1/keys/';

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Construct the V1StorageClient with an instance of ClientBuilder.
     *
     * @param ClientBuilder $clientBuilder
     */
    public function __construct(
        ClientBuilder $clientBuilder
    ) {
        $base_uri = $clientBuilder->getBaseUri() . self::STORAGE_ENDPOINT_PATH;
        $this->client = $clientBuilder
            ->setBaseUri($base_uri)
            ->build()
        ;
    }

    /**
     * Store a value by key.
     *
     * @param string $key
     * @param string $value
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Bergen\Client\Error\UnexpectedResponseError
     */
    public function put($key, $value)
    {
        $relUrl = rawurlencode($key);
        $opts = [
            'body' => $value,
            'headers' => [
                'ContentType' => 'application/octet-stream'
            ],
        ];
        $response = $this->request('PUT', $relUrl, $opts);
        if ($response->getStatusCode() !== 201 && $response->getStatusCode() !== 204) {
            $msg = $this->getStatusLine($response);
            throw new UnexpectedResponseError(
                "Expected HTTP 201 or 204 response to PUT request for {$relUrl}, got \"{$msg}\"."
            );
        }
        return $response;
    }

    /**
     * Get a value by key.
     *
     * @param string $key
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Bergen\Client\Error\UnexpectedResponseError
     */
    public function get($key)
    {
        $relUrl = rawurlencode($key);
        $response = $this->request('GET', $relUrl);
        if ($response->getStatusCode() !== 200) {
            $msg = $this->getStatusLine($response);
            throw new UnexpectedResponseError(
                "Expected HTTP 200 response to GET request for {$relUrl}, got \"{$msg}\"."
            );
        }
        return $response;
    }

    /**
     * Delete a value by key.
     *
     * @param string $key
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws UnexpectedResponseError
     */
    public function delete($key)
    {
        $relUrl = rawurlencode($key);
        $response = $this->request('DELETE', $relUrl);
        if ($response->getStatusCode() !== 204) {
            $msg = $this->getStatusLine($response);
            throw new UnexpectedResponseError(
                "Expected HTTP 204 response to DELETE request for {$relUrl}, got \"{$msg}\"."
            );
        }
        return $response;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $opts
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Bergen\Client\Error\ClientError
     * @throws \Bergen\Client\Error\ServerError
     * @throws \Bergen\Client\Error\UnexpectedResponseError
     */
    private function request($method, $url, $opts = [])
    {
        $opts = array_merge(
            [
                'http_errors' => true,
            ],
            $opts
        );
        try {
            return $this->client->request($method, $url, $opts);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() !== 404) {
                throw new UnexpectedResponseError(
                    "Server responded unexpectedly to {$method} request for {$url}.",
                    null,
                    $e
                );
            }
            return $e->getResponse();
        } catch (ServerException $e) {
            throw new ServerError(
                "Server failed to respond correctly to {$method} request for {$url}.",
                null,
                $e
            );
        } catch (TransferException $e) {
            throw new ClientError(
                "Unable to make {$method} request for {$url}.",
                null,
                $e
            );
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return string
     */
    private function getStatusLine(ResponseInterface $response)
    {
        if (!$response->getReasonPhrase()) {
            return $response->getStatusCode();
        }
        return $response->getStatusCode() . ' ' . $response->getReasonPhrase();
    }
}
