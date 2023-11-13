<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;

class StarshipController {
    private $client;

    public function __construct(Client $client = null) {
        $this->client = $client ?? new Client();
    }

    public function getStarshipsByPerson(Request $request, Response $response, $args): Response {
        $personName = $request->getQueryParams()['person'] ?? '';
        try {
            $peopleResponse = $this->client->get("https://swapi.dev/api/people/?search=$personName");
            $peopleData = json_decode($peopleResponse->getBody(), true);

            if ($peopleData['count'] == 0) {
                $response->getBody()->write(json_encode(["detail" => "Character not found"]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $promises = [];
            foreach ($peopleData['results'][0]['starships'] as $starshipUrl) {
                $promises[] = $this->client->getAsync($starshipUrl);
            }

            $results = Promise\Utils::unwrap($promises);
            $starships = array_map(function ($result) {
                return json_decode($result->getBody(), true);
            }, $results);

            $response->getBody()->write(json_encode($starships));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (RequestException $e) {
            $response->getBody()->write(json_encode(["error" => "Error retrieving starship data"]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
