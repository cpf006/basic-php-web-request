<?php

use PHPUnit\Framework\TestCase;
use App\Middleware\CacheMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Slim\Psr7\Factory\ResponseFactory;
use Symfony\Contracts\Cache\ItemInterface;

class CacheMiddlewareTest extends TestCase
{
    private function mockRequestWithUri($uriPath)
    {
        $uriMock = $this->createMock(\Psr\Http\Message\UriInterface::class);
        $uriMock->method('getPath')->willReturn($uriPath);

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getUri')->willReturn($uriMock);

        return $requestMock;
    }

    public function testProcessWithCacheMiss()
    {
        $uri = '/';
        $expectedResponseData = [
            'statusCode' => 200,
            'headers' => ['Content-Type' => ['application/json']],
            'body' => '{"message":"Hello World"}'
        ];
        $request = $this->mockRequestWithUri($uri);

        // Mock the handler to return a response
        $response = (new ResponseFactory())->createResponse()
            ->withStatus($expectedResponseData['statusCode'])
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($expectedResponseData['body']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $middleware = new CacheMiddleware();
        $processedResponse = $middleware->process($request, $handler);

        $this->assertEquals($expectedResponseData['statusCode'], $processedResponse->getStatusCode());
        $this->assertEquals($expectedResponseData['headers'], $processedResponse->getHeaders());
        $this->assertEquals($expectedResponseData['body'], (string) $processedResponse->getBody());
    }

    public function testProcessWithCacheHit()
    {
        // Mock request
        $uri = '/test-uri';
        $expectedResponseData = [
            'statusCode' => 200,
            'headers' => ['Content-Type' => ['application/json']],
            'body' => '{"message":"Cached response"}'
        ];
        $request = $this->mockRequestWithUri($uri);

        // Mock the FilesystemAdapter to return the expected array structure
        $cache = $this->createMock(FilesystemAdapter::class);
        $cache->method('get')->willReturn($expectedResponseData);
        $middleware = new CacheMiddleware($cache);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $processedResponse = $middleware->process($request, $handler);

        // Assert response matches cached version
        $this->assertEquals($expectedResponseData['statusCode'], $processedResponse->getStatusCode());
        $this->assertEquals($expectedResponseData['headers'], $processedResponse->getHeaders());
        $this->assertEquals($expectedResponseData['body'], (string) $processedResponse->getBody());
    }
}
