# Description 
This project is a REST API server built with Slim PHP, interfacing with the Star Wars API (SWAPI). It provides endpoints to retrieve starships associated with specific characters, species classifications in specified film episodes, and the total population of all planets in the Star Wars galaxy. Designed for efficiency and ease of use, it includes features like asynchronous API calls and comprehensive error handling.

# Running Project:
```
composer install
```
To install dependencies and then:
```
php -S localhost:8000 -t public
```
To run the server. 

# Example URLs:
List of Starships for Luke Skywalker:
```http://localhost:8000/api/starships?person=Luke%20Skywalker```

Species Classification in Episode I:
```http://localhost:8000/api/species?episode=1```

Total Population of Planets in the Galaxy:
```http://localhost:8000/api/planets/population```

# Testing:
Run tests:
```./vendor/bin/phpunit```

# Documentation
### Get Starships for a Character
Endpoint: /api/starships<br>
Method: GET<br>
Parameter: person - The name of the character (e.g., "Luke Skywalker")<br>
Example Request: GET /api/starships?person=Luke%20Skywalker<br>
Example Response:
```
[
  {
    "name": "X-wing",
    "model": "T-65 X-wing",
    "manufacturer": "Incom Corporation",
    "cost_in_credits": "149999",
    "length": "12.5",
    "max_atmosphering_speed": "1050",
    "crew": "1",
    "passengers": "0",
    ...
  }
]
```

### Get Species Classification by Episode
Endpoint: /api/species<br>
Method: GET<br>
Parameter: episode - The episode number (e.g., 1 for the first episode)<br>
Example Request: GET /api/species?episode=1<br>
Example Response:
```
[
  "mammal",
  "artificial",
  "sentient"
]
```

### Get Total Population of All Planets
Endpoint: /api/planets/population<br>
Method: GET<br>
No Parameters<br>
Example Request: GET /api/planets/population<br>
Example Response:
```
{
  "totalPopulation": "100000000000"
}
```





