<?php

namespace Bergen\Client\Test\V1;

use PHPUnit_Framework_TestCase;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;

use Bergen\Client\ClientBuilder;
use Bergen\Client\V1\V1StorageClient;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\RequestInterface;

class V1StorageClientTest extends PHPUnit_Framework_TestCase
{
    private $client;
    private $http;
    private $httpBuilder;
    private $request;
    private $response;

    protected function setUp()
    {
        $this->httpBuilder = $this
            ->getMockBuilder(ClientBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->http = $this
            ->getMockBuilder(ClientInterface::class)
            #->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
        $this->response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMockForAbstractClass()
        ;
        $this->request = $this
            ->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass()
        ;
        $this
            ->httpBuilder
            ->method('build')
            ->willReturn($this->http)
        ;
        $this
            ->http
            ->method('request')
            ->willReturn($this->response)
        ;

        // these are for V1StorageClient::__construct
        $this
            ->httpBuilder
            ->method('getBaseUri')
            ->willReturn('/')
        ;
        $this
            ->httpBuilder
            ->method('setBaseUri')
            ->willReturnSelf()
        ;
        // now call V1StorageClient::__construct
        $this->client = new V1StorageClient($this->httpBuilder);
    }

    /**
     * @expectedException \Bergen\Client\Error\ClientError
     */
    public function testPutWillThrowClientErrorWhenClientFails()
    {
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new TransferException)
        ;

        $this->client->put('mykey', 'myval');
    }

    /**
     * @expectedException \Bergen\Client\Error\ServerError
     */
    public function testPutWillThrowServerErrorWhenServerFails()
    {
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ServerException('Nope', $this->request))
        ;

        $this->client->put('mykey', 'myval');
    }

    /**
     * @expectedException \Bergen\Client\Error\UnexpectedResponseError
     */
    public function testPutWillThrowUnexpectedResponseErrorWhenResponseIsNotAsExpected()
    {
        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200)
        ;

        $this->client->put('mykey', 'myval');
    }

    public function testPutWillReturnPsrResponse()
    {
        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(201)
        ;

        $this->assertSame(
            $this->response,
            $this->client->put('mykey', 'myval')
        );

        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(204)
        ;

        $this->assertSame(
            $this->response,
            $this->client->put('mykey', 'myval')
        );
    }

    /**
     * @expectedException \Bergen\Client\Error\ClientError
     */
    public function testGetWillThrowClientErrorWhenClientFails()
    {
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new TransferException)
        ;

        $this->client->get('mykey');
    }

    /**
     * @expectedException \Bergen\Client\Error\ServerError
     */
    public function testGetWillThrowServerErrorWhenServerFails()
    {
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ServerException('Nope', $this->request))
        ;

        $this->client->get('mykey');
    }

    /**
     * @expectedException \Bergen\Client\Error\UnexpectedResponseError
     */
    public function testGetWillThrowUnexpectedResponseErrorWhenResponseIsNotAsExpected()
    {
        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(301)
        ;

        $this->client->put('mykey', 'myval');
    }

    public function testGetWillReturnPsrResponse()
    {
        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200)
        ;

        $this->assertSame(
            $this->response,
            $this->client->get('mykey')
        );

        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(404)
        ;

        $this->assertSame(
            $this->response,
            $this->client->get('mykey')
        );
    }

    /**
     * @expectedException \Bergen\Client\Error\ClientError
     */
    public function testDeleteWillThrowClientErrorWhenClientFails()
    {
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new TransferException)
        ;

        $this->client->delete('mykey');
    }

    /**
     * @expectedException \Bergen\Client\Error\ServerError
     */
    public function testDeleteWillThrowServerErrorWhenServerFails()
    {
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ServerException('Nope', $this->request))
        ;

        $this->client->delete('mykey');
    }

    /**
     * @expectedException \Bergen\Client\Error\UnexpectedResponseError
     */
    public function testDeletetWillThrowUnexpectedResponseErrorWhenResponseIsNotAsExpected()
    {
        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(200)
        ;

        $this->client->delete('mykey');
    }

    public function testDeleteWillReturnPsrResponse()
    {
        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(204)
        ;

        $this->assertSame(
            $this->response,
            $this->client->delete('mykey')
        );

        $this
            ->response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(404)
        ;

        $this->assertSame(
            $this->response,
            $this->client->delete('mykey')
        );
    }
}
