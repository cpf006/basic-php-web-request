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

