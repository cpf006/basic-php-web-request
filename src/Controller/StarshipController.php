<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;

/**
 * Handles operations related to starships.
 *
 * This controller provides functionalities to interact with swapi
 * for retrieving starship information.
 */
class StarshipController
{
    private $client;

    /**
     * Constructor for the StarshipController.
     *
     * @param Client|null $client The Guzzle HTTP client instance.
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    /**
     * Retrieves starships associated with a specific person.
     *
     * This method fetches data from swapi based on the specified person's name
     * and returns the starships associated with that person.
     *
     * @param Request $request  The PSR-7 request object.
     * @param Response $response The PSR-7 response object.
     * @param array $args Arguments passed to the route.
     * @return Response Modified response object with starship data.
     */
    public function getStarshipsByPerson(Request $request, Response $response, $args): Response
    {
        $personName = $request->getQueryParams()['person'] ?? '';
        try {
            // Get character
            $peopleResponse = $this->client->get("https://swapi.dev/api/people/?search=$personName");
            $peopleData = json_decode($peopleResponse->getBody(), true);

            // Handle case where character not found
            if ($peopleData['count'] == 0) {
                $response->getBody()->write(json_encode(["detail" => "Character not found"]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Get starships
            $promises = [];
            foreach ($peopleData['results'][0]['starships'] as $starshipUrl) {
                $promises[] = $this->client->getAsync($starshipUrl);
            }

            // Add starships to response
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
