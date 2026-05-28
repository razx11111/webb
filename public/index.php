<?php

// Start the Autoloader to find classes automatically
require_once __DIR__ . '/../src/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Load global configuration
if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
}

// Ensure APP_NAME is defined
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Crisis Containment Service');
}

use App\Core\Router;

// Initialize the Router
$router = new Router();


//Define Application Routes


// UI Routes (Returning HTML)
$router->get('/earthquakes', 'DisasterController', 'getEarthquakes');
$router->get('/fires','DisasterController', 'getFires');
$router->get('/floods', 'DisasterController', 'getFloods');
$router->get('/report', 'DisasterController', 'report');
$router->get('/', 'DisasterController', 'index'); 

// API Routes (Used by AJAX, returning JSON)
$router->get('/api/disasters', 'DisasterController', 'getDisasters');
$router->get('/api/earthquakes', 'DisasterController', 'apiGetEarthquakes');
$router->get('/api/fires', 'DisasterController', 'apiGetFires');
$router->get('/api/floods', 'DisasterController', 'apiGetFloods');
$router->get('/api/sync', 'DisasterController', 'sync');
$router->get('/api/cap', 'DisasterController', 'generateCapFeed');
$router->get('/api/cap/alert', 'DisasterController', 'exportSingleCap');

// Run the application
$router->run();
