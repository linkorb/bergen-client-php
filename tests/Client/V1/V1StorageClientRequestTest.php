<?php

namespace Bergen\Client\Test\V1;

use PHPUnit_Framework_TestCase;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionMethod;

use Bergen\Client\ClientBuilder;
use Bergen\Client\Error\ClientError;
use Bergen\Client\Error\ServerError;
use Bergen\Client\Error\UnexpectedResponseError;
use Bergen\Client\V1\V1StorageClient;

class V1StorageClientRequestTest extends PHPUnit_Framework_TestCase
{
    private $storage;
    private $http;
    private $request;
    private $response;

    protected function setUp()
    {
        $builder = $this
            ->getMockBuilder(ClientBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->http = $this
            ->getMockBuilder(ClientInterface::class)
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

        $builder
            ->method('build')
            ->willReturn($this->http)
        ;
        $builder
            ->method('getBaseUri')
            ->willReturn('/')
        ;
        $builder
            ->method('setBaseUri')
            ->willReturnSelf()
        ;

        $this->storage = new V1StorageClient($builder);
    }

    private function invokeRequest($method, $url, array $opts = array())
    {
        $request = new ReflectionMethod(V1StorageClient::class, 'request');
        $request->setAccessible(true);

        return $request->invoke($this->storage, $method, $url, $opts);
    }

    public function testRequestReturnsTheClientResponse()
    {
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->with('PUT', 'my%20key', array(
                'http_errors' => true,
                'body' => 'payload',
            ))
            ->willReturn($this->response)
        ;

        $this->assertSame(
            $this->response,
            $this->invokeRequest('PUT', 'my%20key', array('body' => 'payload'))
        );
    }

    public function testRequestReturnsTheResponseWhenTheClientErrorIs404()
    {
        $this
            ->response
            ->method('getStatusCode')
            ->willReturn(404)
        ;
        $error = new ClientException('missing', $this->request, $this->response);
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'absent', array('http_errors' => true))
            ->willThrowException($error)
        ;

        $this->assertSame(
            $this->response,
            $this->invokeRequest('GET', 'absent')
        );
    }

    public function testRequestThrowsUnexpectedResponseErrorWhenTheClientErrorIsNot404()
    {
        $this
            ->response
            ->method('getStatusCode')
            ->willReturn(409)
        ;
        $error = new ClientException('conflict', $this->request, $this->response);
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException($error)
        ;

        try {
            $this->invokeRequest('POST', 'taken');
            $this->fail('Expected UnexpectedResponseError');
        } catch (UnexpectedResponseError $caught) {
            $this->assertSame(
                'Server responded unexpectedly to POST request for taken.',
                $caught->getMessage()
            );
            $this->assertSame(0, $caught->getCode());
            $this->assertSame($error, $caught->getPrevious());
        }
    }

    public function testRequestThrowsServerErrorWhenTheServerFails()
    {
        $this
            ->response
            ->method('getStatusCode')
            ->willReturn(503)
        ;
        $error = new ServerException('unavailable', $this->request, $this->response);
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException($error)
        ;

        try {
            $this->invokeRequest('DELETE', 'row');
            $this->fail('Expected ServerError');
        } catch (ServerError $caught) {
            $this->assertSame(
                'Server failed to respond correctly to DELETE request for row.',
                $caught->getMessage()
            );
            $this->assertSame(0, $caught->getCode());
            $this->assertSame($error, $caught->getPrevious());
        }
    }

    public function testRequestThrowsClientErrorWhenTheTransferFails()
    {
        $error = new TransferException('connection reset');
        $this
            ->http
            ->expects($this->once())
            ->method('request')
            ->willThrowException($error)
        ;

        try {
            $this->invokeRequest('GET', 'down');
            $this->fail('Expected ClientError');
        } catch (ClientError $caught) {
            $this->assertSame(ClientError::class, get_class($caught));
            $this->assertSame(
                'Unable to make GET request for down.',
                $caught->getMessage()
            );
            $this->assertSame(0, $caught->getCode());
            $this->assertSame($error, $caught->getPrevious());
        }
    }
}
