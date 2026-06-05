<?php

namespace App\Controllers;

use App\Models\Flood;
use App\Models\Fire;
use App\Models\Earthquake;
use App\Services\DataSync;

/**
 * DisasterController
 * 
 * This controller manages the flow of disaster-related data.
 */
class DisasterController {
    
    /**
     * Constructor - Runs automatically when the controller is created.
     * We call our AuthMiddleware here to block anyone who isn't logged in.
     */
    public function __construct() {
        \App\Core\AuthMiddleware::requireLogin();
    }

    public function index() {
        $pageTitle = "Crisis Containment Dashboard";
        require_once __DIR__ . '/../../templates/pages/home.php';
    }

    public function getEarthquakes() {
        $this->showDisasterPage('earthquakes');
    }

    public function getFires() {
        $this->showDisasterPage('fires');
    }

    public function getFloods() {
        $this->showDisasterPage('floods');
    }

    public function getDisasters() {
        header('Content-Type: application/json');
        try {
            $floodModel = new Flood();
            $fireModel = new Fire();
            $earthquakeModel = new Earthquake();
            $data = [
                'floods'      => $floodModel->getAll(20),
                'fires'       => $fireModel->getAll(20),
                'earthquakes' => $earthquakeModel->getAll(20)
            ];
            echo json_encode($data);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function sync() {
        header('Content-Type: application/json');
        try {
            $syncService = new DataSync();
            $syncService->syncExternalData();
            echo json_encode(['status' => 'success', 'message' => 'Data synchronization complete.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function exportCsv() {
        // This method is for exporting data as a CSV file.
        // Get the 'type' and 'country' from the URL.
        $type = $_GET['type'] ?? '';
        $country = $_GET['country'] ?? null;
        
        // Use a match statement to decide which model to use.
        $model = match($type) {
            'flood' => new Flood(),
            'fire' => new Fire(),
            'earthquake' => new Earthquake(),
            default => null
        };

        // If the type is not valid, I show an error message.
        if (!$model) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo "Error: Invalid or missing disaster type for CSV export.";
            return;
        }

        // I fetch more records for the CSV.
        $data = $model->getAll(500, $country);

        // If there is no data, I show a message to the user.
        if (empty($data)) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo "No data available to export for the selected criteria.";
            return;
        }

        // Generate a filename with the current date.
        $filename = "{$type}_data_" . date('Y-m-d_H-i') . ".csv";

        // These headers tell the browser to download the file instead of displaying it.
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Open the PHP output stream to write the CSV directly to the browser.
        $output = fopen('php://output', 'w');

        // Write the column headings first. from the keys of the first row of data.
        fputcsv($output, array_keys($data[0]));

        // Loop through the data and write each row to the CSV file.
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        // close the output stream.
        fclose($output);
    }

    public function report() { $pageTitle = "Disaster Report Generation"; require_once __DIR__ . '/../../templates/pages/report.php'; }

    /**
     * Shows a page for a specific type of disaster.
     * e.g., /disasters/floods
     */
    public function showDisasterPage(string $type) {
        $pageTitle = ucfirst($type) . " Management";
        $templateFile = __DIR__ . "/../../templates/pages/{$type}.php";

        if (file_exists($templateFile)) {
            require_once $templateFile;
        } else {
            http_response_code(404);
            echo "Page not found.";
        }
    }

    /**
     * API endpoint to get data for a specific disaster type.
     * e.g., /api/disasters/fires?country=US
     */
    public function getDisasterData(string $type) {
        header('Content-Type: application/json');
        $country = $_GET['country'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

        // Get the right model based on the 'type' from the URL
        $model = null;
        if ($type === 'floods') {
            $model = new Flood();
        } elseif ($type === 'fires') {
            $model = new Fire();
        } elseif ($type === 'earthquakes') {
            $model = new Earthquake();
        }

        // If the model is not null, get the data and send it as JSON
        if ($model) {
            $data = $model->getAll($limit, $country);
            echo json_encode($data);
        } else {
            // If the type is not valid, send an error message
            http_response_code(400);
            echo json_encode(['error' => 'Invalid disaster type']);
        }
    }

    public function apiGetEarthquakes() {
        $this->getDisasterData('earthquakes');
    }

    public function apiGetFires() {
        $this->getDisasterData('fires');
    }

    public function apiGetFloods() {
        $this->getDisasterData('floods');
    }


    /**
     * API: Check if coordinates are near any disaster and find nearest shelter.
     * Accessible via /api/proximity-check?lat=...&lng=...
     */
    private const PROXIMITY_DANGER_RADIUS_KM = 100;
    private const PROXIMITY_TIME_WINDOW_SECONDS = 3600; // 1 hour

    /**
     * API: Check if coordinates are near any disaster and find nearest shelter.
     * Accessible via /api/proximity-check?lat=...&lng=...
     */
    public function checkProximity() {
        header('Content-Type: application/json');
        
        $userLat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
        $userLng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;

        if ($userLat === null || $userLng === null) {
            echo json_encode(['error' => 'Missing coordinates']);
            return;
        }

        $recentDisasters = $this->getRecentDisasters();
        $detectedDisaster = $this->findNearbyDisaster($userLat, $userLng, $recentDisasters);
        $nearestShelter = null;

        if ($detectedDisaster) {
            $nearestShelter = $this->findNearestShelter($userLat, $userLng);
        }

        echo json_encode([
            'inDanger' => !empty($detectedDisaster),
            'details'  => $detectedDisaster,
            'shelter'  => $nearestShelter
        ]);
    }

    /**
     * Fetches all disasters that have occurred within the defined time window.
     * @return array
     */
    private function getRecentDisasters(): array {
        $floodModel = new Flood();
        $fireModel = new Fire();
        $earthquakeModel = new Earthquake();

        // Add a 'type' key to each record for reliable identification
        $floods = array_map(function($d) { $d['type'] = 'flood'; return $d; }, $floodModel->getAll(50));
        $fires = array_map(function($d) { $d['type'] = 'fire'; return $d; }, $fireModel->getAll(50));
        $earthquakes = array_map(function($d) { $d['type'] = 'earthquake'; return $d; }, $earthquakeModel->getAll(50));

        $disasters = array_merge($floods, $fires, $earthquakes);
        
        // Filter for disasters in the last hour
        $oneHourAgo = time() - self::PROXIMITY_TIME_WINDOW_SECONDS;
        return array_filter($disasters, function($d) use ($oneHourAgo) {
            return strtotime($d['event_time']) >= $oneHourAgo;
        });
    }

    /**
     * Finds the first disaster within the danger radius.
     * @param float $userLat
     * @param float $userLng
     * @param array $disasters
     * @return array|null
     */
    private function findNearbyDisaster(float $userLat, float $userLng, array $disasters): ?array {
        foreach ($disasters as $d) {
            $dist = $this->calculateDistance($userLat, $userLng, (float)$d['latitude'], (float)$d['longitude']);
            
            if ($dist <= self::PROXIMITY_DANGER_RADIUS_KM) {
                // Determine severity based on type
                $severity = 'Moderate';
                if ($d['type'] === 'earthquake') {
                    $mag = (float)$d['magnitude'];
                    $severity = ($mag >= 6) ? 'Extreme' : (($mag >= 4.5) ? 'Severe' : 'Moderate');
                } elseif ($d['type'] === 'fire') {
                    $severity = 'Extreme';
                } elseif ($d['type'] === 'flood') {
                    $severity = 'Severe';
                }

                return [
                    'name' => $d['title'] ?? ($d['region'] ?? 'Natural Hazard'),
                    'type' => ucfirst($d['type']),
                    'severity' => $severity,
                    'distance' => round($dist, 2)
                ];
            }
        }
        return null;
    }

    /**
     * Finds the nearest shelter to a given set of coordinates.
     * @param float $userLat
     * @param float $userLng
     * @return array|null
     */
    private function findNearestShelter(float $userLat, float $userLng): ?array {
        $shelterModel = new \App\Models\Shelter();
        $shelters = $shelterModel->getAll(50);
        $minDist = INF;
        $nearestShelter = null;

        foreach ($shelters as $s) {
            $dist = $this->calculateDistance($userLat, $userLng, (float)$s['latitude'], (float)$s['longitude']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearestShelter = [
                    'name' => $s['name'],
                    'distance' => round($dist, 2),
                    'lat' => (float)$s['latitude'],
                    'lng' => (float)$s['longitude']
                ];
            }
        }
        return $nearestShelter;
    }

    /**
     * Helper: Haversine Formula for distance between two points in km.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}
