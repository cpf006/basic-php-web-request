<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PlanetController
{
    private $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    public function getTotalPopulation(Request $request, Response $response, $args): Response
    {
        try {
            // Get planet
            $planetsResponse = $this->client->get("https://swapi.dev/api/planets/");
            $planetsData = json_decode($planetsResponse->getBody(), true);

            // Calculate population
            $totalPopulation = 0;
            foreach ($planetsData['results'] as $planet) {
                $population = $planet['population'] === 'unknown' ? 0 : (int)$planet['population'];
                $totalPopulation += $population;
            }

            $response->getBody()->write(json_encode(['totalPopulation' => $totalPopulation]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (RequestException $e) {
            $response->getBody()->write(json_encode(["error" => "Error retrieving planet data"]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
