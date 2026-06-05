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
        $this->requireAdmin();

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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->sendJsonError(405, 'Method Not Allowed');
        }
        
        $this->requireAdmin();

        $name = trim($_POST['name'] ?? '');
        $lat = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
        $lng = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);
        $cap = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);

        if (empty($name) || $lat === false || $lng === false) {
            return $this->sendJsonError(400, 'Invalid data. Please provide a name and a valid map location.');
        }
        
        if ($cap !== null && $cap < 0) {
            return $this->sendJsonError(400, 'Capacity cannot be negative.');
        }

        $model = new Shelter();
        $success = $model->create([
            'name'      => $name,
            'latitude'  => $lat,
            'longitude' => $lng,
            'capacity'  => $cap,
        ]);

        if ($success) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Shelter added successfully.']);
        } else {
            $this->sendJsonError(500, 'Failed to save shelter to the database.');
        }
    }

    /**
     * Ensures that the current user is an administrator.
     * If not, it sends a 403 Forbidden response and terminates the script.
     */
    private function requireAdmin() {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            $this->sendJsonError(403, 'Forbidden: Administrator access required.');
            exit();
        }
    }

    /**
     * Sends a JSON error response with an appropriate status code.
     *
     * @param int $statusCode The HTTP status code.
     * @param string $message The error message.
     */
    private function sendJsonError(int $statusCode, string $message) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
}
