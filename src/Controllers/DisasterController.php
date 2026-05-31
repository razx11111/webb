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
        $type = $_GET['type'] ?? '';
        $country = $_GET['country'] ?? null;
        
        // Dynamically instantiate the right model based on the URL parameter
        $model = match($type) {
            'flood' => new Flood(),
            'fire' => new Fire(),
            'earthquake' => new Earthquake(),
            default => null
        };

        if (!$model) {
            http_response_code(400);
            die("Error: Invalid or missing disaster type for CSV export.");
        }

        // We fetch more records for the CSV so the user gets a solid dataset
        $data = $model->getAll(500, $country); 

        if (empty($data)) {
            die("No data available to export.");
        }

        // Generate a nice filename with the current date
        $filename = "{$type}_data_" . date('Y-m-d_H-i') . ".csv";

        // Tell the browser to expect a file download instead of HTML
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Open the PHP output stream to write the CSV directly
        $output = fopen('php://output', 'w');

        // Put the column headings first (we get them from the keys of the first row)
        fputcsv($output, array_keys($data[0]));

        // Write each row of data into the CSV
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit();
    }

    public function getFloods() { $pageTitle = "Floods Management"; require_once __DIR__ . '/../../templates/pages/floods.php'; }
    public function getEarthquakes() { $pageTitle = "Earthquakes Management"; require_once __DIR__ . '/../../templates/pages/earthquakes.php'; }
    public function getFires() { $pageTitle = "Fires Management"; require_once __DIR__ . '/../../templates/pages/fires.php'; }
    public function report() { $pageTitle = "Disaster Report Generation"; require_once __DIR__ . '/../../templates/pages/report.php'; }
    public function apiGetFloods() { 
        $country = $_GET['country'] ?? null;
        echo json_encode((new Flood())->getAll(50, $country)); 
    }
    public function apiGetFires() { 
        $country = $_GET['country'] ?? null;
        echo json_encode((new Fire())->getAll(50, $country)); 
    }
    public function apiGetEarthquakes() { 
        $country = $_GET['country'] ?? null;
        echo json_encode((new Earthquake())->getAll(50, $country)); 
    }

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

        $dangerRadius = 100; // km
        $inDanger = false;
        $detectedDisaster = null;
        $currentTime = time();
        $oneHourAgo = $currentTime - 3600; // 3600 seconds = 1 hour

        // 1. Get all recent disasters (fetching more to ensure we cover the last hour)
        $floodModel = new Flood();
        $fireModel = new Fire();
        $earthquakeModel = new Earthquake();

        $disasters = array_merge(
            $floodModel->getAll(50),
            $fireModel->getAll(50),
            $earthquakeModel->getAll(50)
        );

        // 2. Check if user is near any disaster that happened in the last hour
        foreach ($disasters as $d) {
            $eventTime = strtotime($d['event_time']);
            
            // Skip disasters older than 1 hour
            if ($eventTime < $oneHourAgo) {
                continue;
            }

            $dist = $this->calculateDistance($userLat, $userLng, (float)$d['latitude'], (float)$d['longitude']);
            if ($dist <= $dangerRadius) {
                $inDanger = true;
                
                // Determine type and severity
                $type = 'unknown';
                $severity = 'Moderate';
                
                if (isset($d['magnitude'])) {
                    $type = 'Earthquake';
                    $mag = (float)$d['magnitude'];
                    $severity = ($mag >= 6) ? 'Extreme' : (($mag >= 4.5) ? 'Severe' : 'Moderate');
                } elseif (strpos($this->get_class_name($d), 'Flood') !== false || isset($d['title']) && strpos(strtolower($d['title']), 'flood') !== false) {
                    $type = 'Flood';
                    $severity = 'Severe';
                } else {
                    $type = 'Fire';
                    $severity = 'Extreme';
                }

                $detectedDisaster = [
                    'name' => $d['title'] ?? ($d['region'] ?? 'Natural Hazard'),
                    'type' => $type,
                    'severity' => $severity,
                    'distance' => round($dist, 2)
                ];
                break;
            }
        }

        // 3. If in danger, find the nearest shelter
        $nearestShelter = null;
        if ($inDanger) {
            $shelterModel = new \App\Models\Shelter();
            $shelters = $shelterModel->getAll(50);
            $minDist = INF;

            foreach ($shelters as $s) {
                $dist = $this->calculateDistance($userLat, $userLng, (float)$s['latitude'], (float)$s['longitude']);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $nearestShelter = [
                        'name' => $s['name'],
                        'distance' => round($dist, 2)
                    ];
                }
            }
        }

        echo json_encode([
            'inDanger' => $inDanger,
            'details'  => $detectedDisaster ?? null,
            'shelter'  => $nearestShelter
        ]);
    }

    /**
     * Helper to guess type if model info is lost in array merge
     */
    private function get_class_name($var) {
        return gettype($var); // Simplified for now since we merged arrays
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
