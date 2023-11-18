<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Controller\SpeciesController;

class SpeciesControllerTest extends TestCase
{
    private $controller;
    private $request;
    private $response;

    public function testGetSpeciesByEpisodeSuccess()
    {
        // Mock responses for the film and species data
        $filmResponse = new GuzzleResponse(200, [], json_encode([
            "species" => ["https://swapi.dev/api/species/1/", "https://swapi.dev/api/species/2/"]
        ]));
        $speciesResponse = new GuzzleResponse(200, [], json_encode(["classification" => "mammal"]));
        $speciesTwoResponse = new GuzzleResponse(200, [], json_encode(["classification" => "mammal"]));

        // Create a mock handler and add the responses
        $mock = new MockHandler([$filmResponse, $speciesResponse, $speciesTwoResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create request and response
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/?episode=1');
        $response = (new ResponseFactory())->createResponse();
        $controller = new SpeciesController($client);
        $response = $controller->getSpeciesByEpisode($request, $response, []);

        // Assert classification list is returned with no duplicates
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertEquals(['mammal'], $responseData);
    }

    public function testGetSpeciesByEpisodeNotFound()
    {
        // Mock a 404 response for the film
        $filmResponse = new GuzzleResponse(404, [], '');

        // Create a mock handler and add the response
        $mock = new MockHandler([$filmResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create request and response
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/?episode=invalid');
        $response = (new ResponseFactory())->createResponse();
        $controller = new SpeciesController($client);
        $response = $controller->getSpeciesByEpisode($request, $response, []);

        // Assert 404 is returned with proper error message
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertEquals(["error" => "Episode not found"], $responseData);
    }
}
