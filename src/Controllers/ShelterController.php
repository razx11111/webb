<?php

namespace App\Controllers;

use App\Models\Shelter;
use App\Core\AuthMiddleware;

/**
 * ShelterController
 * 
 * Manages shelter locations, primarily for admin setup and user viewing.
 */
class ShelterController {

    public function __construct() {
        // Most actions here require a session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Admin View: Set up shelters on the map.
     */
    public function adminIndex() {
        // Protect this route: only admins can manage shelters
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header("Location: /login");
            exit();
        }

        $pageTitle = "Manage Emergency Shelters";
        require_once __DIR__ . '/../../templates/pages/shelters_admin.php';
    }

    /**
     * API: Get all shelters as JSON.
     */
    public function apiGetShelters() {
        header('Content-Type: application/json');
        $model = new Shelter();
        echo json_encode($model->getAll(100));
    }

    /**
     * Action: Add a new shelter (POST).
     */
    public function addShelter() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die("Unauthorized");
        }

        $name = trim($_POST['name'] ?? '');
        $lat  = $_POST['latitude'] ?? '';
        $lng  = $_POST['longitude'] ?? '';
        $cap  = $_POST['capacity'] ?? null;

        if (empty($name) || empty($lat) || empty($lng)) {
            die("Please provide name and location (click on the map).");
        }

        $model = new Shelter();
        $model->create([
            'name'      => $name,
            'latitude'  => $lat,
            'longitude' => $lng,
            'capacity'  => $cap
        ]);

        header("Location: /admin/shelters?success=1");
        exit();
    }
}
