<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\ClientException;

class SpeciesController {
    private $client;

    public function __construct(Client $client = null) {
        $this->client = $client ?? new Client();
    }

    private function errorResponse(String $error) {
        $response->getBody()->write(json_encode(["error" => $error]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

    public function getSpeciesByEpisode(Request $request, Response $response, $args): Response {
        $episodeId = $request->getQueryParams()['episode'] ?? '';

        // Get Episode 
        try {
            $filmResponse = $this->client->get("https://swapi.dev/api/films/$episodeId");
            $filmData = json_decode($filmResponse->getBody(), true);
        } catch (ClientException $e) {
            $response->getBody()->write(json_encode(["error" => "Episode not found"]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        } catch (RequestException $e) {
            return $this->errorResponse("Error retrieving episode data");
        }

        // Get Species
        try {
            $promises = [];
            foreach ($filmData['species'] as $speciesUrl) {
                $promises[] = $this->client->getAsync($speciesUrl);
            }

            $results = Promise\Utils::unwrap($promises);
            $species = array_map(function ($result) {
                return json_decode($result->getBody(), true)["classification"];
            }, $results);

            // Remove duplicates
            $species = array_values(array_unique($species));

            $response->getBody()->write(json_encode($species));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (RequestException $e) {
            return $this->errorResponse("Error retrieving species data");
        }
    }
}
