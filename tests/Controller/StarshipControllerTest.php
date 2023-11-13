<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Controller\StarshipController;

class StarshipControllerTest extends TestCase
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

    public function testFetchingStarshipsForValidCharacter() {
        $peopleResponse = new GuzzleResponse(200, [], json_encode([
            "count" => 1, 
            "results" => [[ 
                "name" => "Luke Skywalker",
                "starships" => ["https://swapi.dev/api/starships/12/"]
            ]]
        ]));
        $starshipResponse = new GuzzleResponse(200, [], json_encode([
            "name" => "X-wing",
            "model" => "T-65 X-wing",
            "manufacturer" => "Incom Corporation",
            "starship_class" => "Starfighter",
            "url" => "https://swapi.dev/api/starships/12/"
        ]));

        // Create a mock handler and add the responses
        $mock = new MockHandler([$peopleResponse, $starshipResponse]);
        $handlerStack = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handlerStack]);

        // Inject the mocked client into the controller
        $this->controller = new StarshipController($this->client);

        // Simulate a request with a valid character
        $this->request = $this->request->withQueryParams(['person' => 'Luke Skywalker']);
        $response = $this->controller->getStarshipsByPerson($this->request, $this->response, []);

        // Assert the response contains the expected data
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($responseData);
        $this->assertEquals("X-wing", $responseData[0]['name']);
    }

    public function testFetchingStarshipsForInvalidCharacter()
    {
        $notFoundResponse = new GuzzleResponse(200, [], json_encode([
            "count" => 0, 
            "results" => []
        ]));

        // Create a mock handler and add the response
        $mock = new MockHandler([$notFoundResponse]);
        $handlerStack = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handlerStack]);

        // Inject the mocked client into the controller
        $this->controller = new StarshipController($this->client);

        // Simulate a request with an invalid character
        $this->request = $this->request->withQueryParams(['person' => 'Invalid Character']);
        $response = $this->controller->getStarshipsByPerson($this->request, $this->response, []);

        // Decode the JSON response and assert it contains the expected detail
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('detail', $responseData);
        $this->assertEquals('Character not found', $responseData['detail']);
    }

    public function testFetchingStarshipsFailure()
    {
        $notFoundResponse = new GuzzleResponse(500, []);

        $mock = new MockHandler([$notFoundResponse]);
        $handlerStack = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handlerStack]);

        $this->controller = new StarshipController($this->client);
        $this->request = $this->request->withQueryParams(['person' => '']);
        $response = $this->controller->getStarshipsByPerson($this->request, $this->response, []);

        // Assert 500
        $this->assertEquals(500, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Error retrieving starship data', $responseData['error']);
    }
}