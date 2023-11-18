<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\ClientException;

/**
 * Handles operations related to species.
 *
 * This controller provides functionalities to interact with swapi
 * for retrieving species information.
 */
class SpeciesController
{
    private $client;

    /**
     * Constructor for the SpeciesController.
     *
     * @param Client|null $client The Guzzle HTTP client instance.
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    /**
     * Generates an error response.
     *
     * @param string $error The error message.
     * @return Response The response object with the error message.
     */
    private function errorResponse(string $error)
    {
        $response->getBody()->write(json_encode(["error" => $error]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Retrieves species by episode.
     *
     * This method fetches data from swapi based on the specified episode
     * and returns the species involved in that episode.
     *
     * @param Request $request  The PSR-7 request object.
     * @param Response $response The PSR-7 response object.
     * @param array $args Arguments passed to the route.
     * @return Response Modified response object with species data.
     */
    public function getSpeciesByEpisode(Request $request, Response $response, $args): Response
    {
        $episodeId = $request->getQueryParams()['episode'] ?? '';

        // Get episode
        try {
            $filmResponse = $this->client->get("https://swapi.dev/api/films/$episodeId");
            $filmData = json_decode($filmResponse->getBody(), true);
        } catch (ClientException $e) {
            $response->getBody()->write(json_encode(["error" => "Episode not found"]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        } catch (RequestException $e) {
            return $this->errorResponse("Error retrieving episode data");
        }

        // Get species
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
