<?php

// 1. Init
// We load the global settings and the Autoloader
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Autoloader.php';

//  We register the Autoloader as PHP to know how to find its classes
App\Core\Autoloader::register();

// 2. We instanciate the Controller/
use App\Controllers\DisasterController;
$controller = new DisasterController();

// 3. We read the Request.
// If in the URL we have "?action=something", we check it.
// If it's not there, we use 'home' as default
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

switch ($action) {
    case 'floods':
        // We show a detailed view of the floods
        $controller->getFloods();
        break;

    case 'earthquakes':
        // We show a detailed view of the earthquakes
        $controller->getEarthquakes();
        break;

    case 'fires':
        // We show a detailed view of the fires
        $controller->getFires();
        break;

    case 'api_data':
        // If the client asks for "?action=api_data", we return the JSON with the disasters
        $controller->getDisasters();
        break;

    case 'sync':
        // If the client presses the Sync button, we call the method that fetches the new data
        $controller->sync();
        break;
    case 'report':
        // When someone accesses "?action=report"
        $controller->report();
        break;
    case 'home':
    default:
        // Default case (if someone enters http://localhost:8080/)
        // We output the home interface
        $controller->index();
        break;
}