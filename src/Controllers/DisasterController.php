<?php

namespace App\Controllers;

use App\Core\AuthMiddleware;
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
    
    public function __construct() {
        // Some methods might be public, but administrative ones are protected
    }

    public function index() {
        AuthMiddleware::requireLogin();
        $pageTitle = "Crisis Containment Dashboard";
        require_once __DIR__ . '/../../templates/pages/home.php';
    }

    public function getDisasters() {
        AuthMiddleware::requireLogin();
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
        AuthMiddleware::requireLogin();
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

    /**
     * GET /admin/test-alert
     * Displays a raw HTML form to manually inject alerts for testing.
     */
    public function showTestAlertForm() {
        AuthMiddleware::requireLogin();
        // Raw HTML as requested (no styles)
        echo '<h2>Create Manual Alert (Test Tool)</h2>';
        echo '<p>Use this to test the proximity alert system. Set coordinates close to yours.</p>';
        echo '<form action="/admin/test-alert" method="POST">';
        echo 'Type: <select name="type"><option value="flood">Flood</option><option value="fire">Fire</option><option value="earthquake">Earthquake</option></select><br><br>';
        echo 'Title/Region: <input type="text" name="title" placeholder="e.g. Test Danger" required><br><br>';
        echo 'Latitude: <input type="text" name="latitude" placeholder="47.1234" required><br><br>';
        echo 'Longitude: <input type="text" name="longitude" placeholder="27.5678" required><br><br>';
        echo 'Magnitude (if EQ): <input type="text" name="magnitude" placeholder="5.5"><br><br>';
        echo '<button type="submit">Inject Alert</button>';
        echo '</form>';
        echo '<br><a href="/">Back to Dashboard</a>';
    }

    /**
     * POST /admin/test-alert
     * Processes the manual injection of a disaster record.
     */
    public function createTestAlert() {
        AuthMiddleware::requireLogin();
        $type = $_POST['type'] ?? '';
        $title = $_POST['title'] ?? '';
        $lat = $_POST['latitude'] ?? '';
        $lng = $_POST['longitude'] ?? '';
        $mag = $_POST['magnitude'] ?? null;

        $data = [
            'external_id' => 'MANUAL-' . time(),
            'latitude'    => $lat,
            'longitude'   => $lng,
            'event_time'  => date('Y-m-d H:i:s'),
            'source'      => 'Manual Test Injection'
        ];

        if ($type === 'earthquake') {
            $data['region'] = $title;
            $data['magnitude'] = $mag;
            $model = new Earthquake();
        } elseif ($type === 'flood') {
            $data['title'] = $title;
            $model = new Flood();
        } else {
            $data['title'] = $title;
            $model = new Fire();
        }

        $model->create($data);
        echo "Alert injected successfully! <br><br>";
        echo "<a href='/admin/test-alert'>Inject another one</a> | <a href='/'>Go to Dashboard to see pop-up</a>";
    }

    public function checkProximity() {
        AuthMiddleware::requireLogin();
        header('Content-Type: application/json');
        $userLat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
        $userLng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;

        if ($userLat === null || $userLng === null) {
            echo json_encode(['inDanger' => false]); return;
        }

        $dangerRadius = 100; // km
        $currentTime = time();
        $oneHourAgo = $currentTime - 3600;

        $floodModel = new Flood();
        $fireModel = new Fire();
        $earthquakeModel = new Earthquake();

        $disasters = array_merge($floodModel->getAll(50), $fireModel->getAll(50), $earthquakeModel->getAll(50));

        foreach ($disasters as $d) {
            $eventTime = strtotime($d['event_time']);
            if ($eventTime < $oneHourAgo) continue;

            $dist = $this->calculateDistance($userLat, $userLng, (float)$d['latitude'], (float)$d['longitude']);
            if ($dist <= $dangerRadius) {
                $type = isset($d['magnitude']) ? 'Earthquake' : (strpos(strtolower($d['title'] ?? ''), 'flood') !== false ? 'Flood' : 'Fire');
                
                $nearestShelter = null;
                $shelters = (new \App\Models\Shelter())->getAll(50);
                $minDist = INF;
                foreach ($shelters as $s) {
                    $sDist = $this->calculateDistance($userLat, $userLng, (float)$s['latitude'], (float)$s['longitude']);
                    if ($sDist < $minDist) {
                        $minDist = $sDist;
                        $nearestShelter = ['name' => $s['name'], 'distance' => round($sDist, 2), 'lat' => (float)$s['latitude'], 'lng' => (float)$s['longitude']];
                    }
                }

                echo json_encode([
                    'inDanger' => true,
                    'details'  => ['name' => $d['title'] ?? ($d['region'] ?? 'Danger'), 'type' => $type],
                    'shelter'  => $nearestShelter
                ]);
                return;
            }
        }
        echo json_encode(['inDanger' => false]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        return $R * (2 * atan2(sqrt($a), sqrt(1-$a)));
    }

    public function getFloods() {
        AuthMiddleware::requireLogin();
        $pageTitle = "Flood Monitoring";
        require_once __DIR__ . '/../../templates/pages/floods.php'; 
    }
    public function getEarthquakes() {
        AuthMiddleware::requireLogin();
        $pageTitle = "Earthquake Activity";
        require_once __DIR__ . '/../../templates/pages/earthquakes.php'; 
    }
    public function getFires() {
        AuthMiddleware::requireLogin();
        $pageTitle = "Wildfire Alerts";
        require_once __DIR__ . '/../../templates/pages/fires.php'; 
    }
    public function report() {
        AuthMiddleware::requireLogin();
        require_once __DIR__ . '/../../templates/pages/report.php';
    }
    public function apiGetFloods() {
        AuthMiddleware::requireLogin();
        echo json_encode((new Flood())->getAll(50));
    }
    public function apiGetFires() {
        AuthMiddleware::requireLogin();
        echo json_encode((new Fire())->getAll(50));
    }
    public function apiGetEarthquakes() {
        AuthMiddleware::requireLogin();
        echo json_encode((new Earthquake())->getAll(50));
    }

    public function exportCsv() {
        AuthMiddleware::requireLogin();
        $type = $_GET['type'] ?? '';
        $model = match($type) { 'flood' => new Flood(), 'fire' => new Fire(), 'earthquake' => new Earthquake(), default => null };
        if (!$model) return;
        $data = $model->getAll(500);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) fputcsv($output, $row);
        fclose($output);
    }
}
