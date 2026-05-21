<?php

// 1. Inițializarea
// Încărcăm setările globale și Autoloader-ul
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Autoloader.php';

// Înregistrăm Autoloader-ul ca PHP să știe să găsească clasele singur
App\Core\Autoloader::register();

// 2. Instanțiem Controller/
use App\Controllers\DisasterController;
$controller = new DisasterController();

// 3. Citim Request-ul.
// Daca in URL avem "?action=ceva", îl luăm. Dacă nu, punem valoarea default 'home'.
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

switch ($action) {
    case 'api_data':
        // Dacă clientul cere "?action=api_data", returnăm JSON cu dezastrele
        $controller->getDisasters();
        break;

    case 'sync':
        // Dacă clientul apasă butonul de Sync, chemăm metoda care aduce date de pe net
        $controller->sync();
        break;
    case 'report':
        // Când cineva accesează ?action=report
        $controller->report();
        break;
    case 'home':
    default:
        // Cazul default (dacă cineva intră doar pe http://localhost:8080/)
        // Îi afișăm interfața grafică (HTML-ul)
        $controller->index();
        break;
}