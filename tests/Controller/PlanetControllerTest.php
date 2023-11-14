<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Controller\PlanetController;

class PlanetControllerTest extends TestCase
{
    private $controller;
    private $client;
    private $request;
    private $response;

    protected function setUp(): void
    {
        $this->request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $this->response = (new ResponseFactory())->createResponse();
    }

    public function testGetTotalPopulationSuccess()
    {
        // Mock a successful response
        $mockResponseData = [
            "results" => [
                ["name" => "Tatooine", "population" => "2"],
                ["name" => "Alderaan", "population" => "1"]
            ]
        ];
        $mockResponse = new GuzzleResponse(200, [], json_encode($mockResponseData));

        // Create a mock handler and add the response
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->controller = new PlanetController($client);
        $response = $this->controller->getTotalPopulation($this->request, $this->response, []);
    
        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('totalPopulation', $responseData);
        $this->assertEquals(3, $responseData['totalPopulation']);
    }

    public function testGetTotalPopulationFailure()
    {
        $mockResponse = new GuzzleResponse(500, []);
    
        // Create a mock handler and add the response
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->controller = new PlanetController($client);
        $response = $this->controller->getTotalPopulation($this->request, $this->response, []);
    
        // Assert the response status code is 500
        $this->assertEquals(500, $response->getStatusCode());
    
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Error retrieving planet data', $responseData['error']);
    }
}
