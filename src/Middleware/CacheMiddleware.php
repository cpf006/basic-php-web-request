<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Slim\Psr7\Factory\ResponseFactory;

class CacheMiddleware implements MiddlewareInterface
{
    private $cache;

    public function __construct(?FilesystemAdapter $cache = null)
    {
        $this->cache = $cache ?? new FilesystemAdapter();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $queryParams = $request->getQueryParams();

        $serializedParams = md5(serialize($queryParams));

        // Generate a cache key based on the request
        $cacheKey = md5($uri . '_' . $serializedParams);

        // Try to fetch the response from the cache
        $cachedData = $this->cache->get($cacheKey, function (ItemInterface $item) use ($handler, $request) {
            $item->expiresAfter(3600); // Cache expiration time in seconds
            $response = $handler->handle($request);

            // Store only necessary parts of the response
            return [
                'statusCode' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => (string) $response->getBody(),
            ];
        });

        // Reconstruct the response
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse($cachedData['statusCode']);

        foreach ($cachedData['headers'] as $name => $values) {
            foreach ($values as $value) {
                $response = $response->withAddedHeader($name, $value);
            }
        }

        $response->getBody()->write($cachedData['body']);

        return $response;
    }
}
