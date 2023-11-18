<?php

use Slim\Factory\AppFactory;
use App\Middleware\CacheMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Load settings
$settings = require __DIR__ . '/../settings.php';

// Create app
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware($settings['displayErrorDetails'], true, true);

// Add Cache Middleware
$app->add(new CacheMiddleware());

// Register routes
(require __DIR__ . '/../routes.php')($app);

// Run app
$app->run();
