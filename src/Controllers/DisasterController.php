<?php

namespace App\Controllers;

use App\Models\Flood;
use App\Models\Fire;
use App\Models\Earthquake;
use App\Services\DataSync;
use App\Services\CAPService;

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

    public function generateCapFeed() {
        $type = $_GET['type'] ?? 'all';
        $limit = isset($_GET['latest']) ? (int)$_GET['latest'] : 15;
        $floodModel = new Flood();
        $fireModel = new Fire();
        $earthquakeModel = new Earthquake();
        $disasters = [];

        if ($type === 'flood' || $type === 'all') {
            foreach ($floodModel->getAll($limit) as $f) { $disasters[] = ['type' => 'flood', 'data' => $f]; }
        }
        if ($type === 'fire' || $type === 'all') {
            foreach ($fireModel->getAll($limit) as $f) { $disasters[] = ['type' => 'fire', 'data' => $f]; }
        }
        if ($type === 'earthquake' || $type === 'all') {
            foreach ($earthquakeModel->getAll($limit) as $e) { $disasters[] = ['type' => 'earthquake', 'data' => $e]; }
        }

        usort($disasters, function($a, $b) {
            return strtotime($b['data']['event_time']) - strtotime($a['data']['event_time']);
        });

        if (count($disasters) > $limit) $disasters = array_slice($disasters, 0, $limit);

        header('Content-Type: application/rss+xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0" xmlns:cap="urn:oasis:names:tc:emergency:cap:1.2">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>CoA Disaster Alert Feed</title>' . "\n";
        echo '<link>http://localhost:8080/</link>' . "\n";
        echo '<description>Latest CAP 1.2 alerts</description>' . "\n";
        
        $capService = new CAPService();
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        foreach ($disasters as $item) {
            $data = $item['data'];
            $internalXmlLink = $baseUrl . "/api/cap/alert?type=" . $item['type'] . "&id=" . $data['id'];
            echo '<item>' . "\n";
            echo '<title>' . htmlspecialchars($data['title'] ?? $data['region']) . '</title>' . "\n";
            echo '<pubDate>' . date('r', strtotime($data['event_time'])) . '</pubDate>' . "\n";
            echo '<link>' . htmlspecialchars($internalXmlLink) . '</link>' . "\n";
            echo '<guid isPermaLink="true">' . htmlspecialchars($internalXmlLink) . '</guid>' . "\n";
            $singleXml = $capService->generateXml($data, $item['type']);
            echo preg_replace('/<\?xml[^>]+\?>\s*|<\?xml-stylesheet[^>]+\?>\s*/i', '', $singleXml) . "\n";
            echo '</item>' . "\n";
        }
        echo '</channel></rss>';
    }

    public function exportSingleCap() {
        // We look for 'id' but also handle cases where XML entities might mess up the key (amp;id)
        $type = $_GET['type'] ?? '';
        $id = $_GET['id'] ?? ($_GET['amp;id'] ?? '');

        if (!$type || !$id) {
            http_response_code(400);
            die("Error: Missing disaster type or ID.");
        }

        $model = match($type) {
            'flood' => new Flood(),
            'fire' => new Fire(),
            'earthquake' => new Earthquake(),
            default => null
        };

        if (!$model) {
            http_response_code(404);
            die("Error: Invalid disaster type.");
        }

        $data = $model->getById($id);
        if (!$data) {
            http_response_code(404);
            die("Error: Alert with ID $id not found in $type table.");
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo (new CAPService())->generateXml($data, $type);
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
}
