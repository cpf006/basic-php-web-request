<?php

use Slim\Routing\RouteCollectorProxy;
use App\Controller\StarshipController;
use App\Controller\SpeciesController;
use App\Controller\PlanetController;

return function ($app) {
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Route for starships, accepts a 'person' query parameter
        $group->get('/starships', [StarshipController::class, 'getStarshipsByPerson']);

        // Route for species, accepts an 'episode' query parameter
        $group->get('/species', [SpeciesController::class, 'getSpeciesByEpisode']);

        // Route for planet population
        $group->get('/planets/population', [PlanetController::class, 'getTotalPopulation']);
    });
};
