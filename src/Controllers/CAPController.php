<?php

namespace App\Controllers;

use App\Models\Flood;
use App\Models\Fire;
use App\Models\Earthquake;
use App\Services\CAPService;

/**
 * CAPController
 * 
 * Handles all logic related to Common Alerting Protocol (CAP) exports.
 * This includes generating RSS feeds with embedded CAP and individual XML alerts.
 */
class CAPController {

    /**
     * CAP RSS Feed Endpoint
     * 
     * Generates a standard RSS 2.0 feed containing CAP 1.2 alerts.
     * Accessible via /api/cap?type=all&latest=15
     */
    public function generateCapFeed() {
        $type = $_GET['type'] ?? 'all';
        $limit = isset($_GET['latest']) ? (int)$_GET['latest'] : 15;

        // Lazy Sync Logic: Ensure data is fresh
        $syncFile = sys_get_temp_dir() . '/coa_last_sync.txt';
        $currentTime = time();
        $lastSync = file_exists($syncFile) ? (int)file_get_contents($syncFile) : 0;

        // If more than 10 minutes (600 seconds) have passed, trigger sync
        if (($currentTime - $lastSync) > 600) {
            try {
                $syncService = new \App\Services\DataSync();
                $syncService->syncExternalData();
                file_put_contents($syncFile, $currentTime);
            } catch (\Exception $e) {
                // Log error but continue to serve whatever data we have
                error_log("Lazy Sync failed: " . $e->getMessage());
            }
        }

        $floodModel = new Flood();
        $fireModel = new Fire();
        $earthquakeModel = new Earthquake();

        $disasters = [];

        // Aggregate disasters based on type
        if ($type === 'flood' || $type === 'all') {
            foreach ($floodModel->getAll($limit) as $fl) { $disasters[] = ['type' => 'flood', 'data' => $fl]; }
        }
        if ($type === 'fire' || $type === 'all') {
            foreach ($fireModel->getAll($limit) as $f) { $disasters[] = ['type' => 'fire', 'data' => $f]; }
        }
        if ($type === 'earthquake' || $type === 'all') {
            foreach ($earthquakeModel->getAll($limit) as $e) { $disasters[] = ['type' => 'earthquake', 'data' => $e]; }
        }

        // Sort by event time descending
        usort($disasters, function($a, $b) {
            return strtotime($b['data']['event_time']) - strtotime($a['data']['event_time']);
        });

        // Limit the final collection
        if (count($disasters) > $limit) $disasters = array_slice($disasters, 0, $limit);

        header('Content-Type: application/rss+xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0" xmlns:cap="urn:oasis:names:tc:emergency:cap:1.2">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>CoA Disaster Alert Feed</title>' . "\n";
        echo '<link>http://localhost:8080/</link>' . "\n";
        echo '<description>Latest CAP 1.2 alerts from Crisis Containment Service</description>' . "\n";
        
        $capService = new CAPService();
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        foreach ($disasters as $item) {
            $data = $item['data'];
            // Pointing directly to our internal CAP XML exporter
            $internalXmlLink = $baseUrl . "/api/cap/alert?type=" . $item['type'] . "&id=" . $data['id'];

            echo '<item>' . "\n";
            echo '<title>' . htmlspecialchars($data['title'] ?? $data['region']) . '</title>' . "\n";
            echo '<pubDate>' . date('r', strtotime($data['event_time'])) . '</pubDate>' . "\n";
            echo '<link>' . htmlspecialchars($internalXmlLink) . '</link>' . "\n";
            echo '<guid isPermaLink="true">' . htmlspecialchars($internalXmlLink) . '</guid>' . "\n";
            
            // Generate the XML and strip headers for embedding
            $singleXml = $capService->generateXml($data, $item['type']);
            echo preg_replace('/<\?xml[^>]+\?>\s*|<\?xml-stylesheet[^>]+\?>\s*/i', '', $singleXml) . "\n";
            
            echo '</item>' . "\n";
        }
        echo '</channel></rss>';
    }

    /**
     * Endpoint for a single CAP XML message
     * Returns a pure CAP 1.2 XML for a specific record.
     */
    public function exportSingleCap() {
        // Handle potential XML entity encoding in keys (amp;id)
        $type = $_GET['type'] ?? '';
        $id = $_GET['id'] ?? ($_GET['amp;id'] ?? '');

        if (!$type || !$id) {
            http_response_code(400);
            die("Missing type or id");
        }

        $model = match($type) {
            'flood' => new Flood(),
            'fire' => new Fire(),
            'earthquake' => new Earthquake(),
            default => null
        };

        if (!$model || !($data = $model->getById($id))) {
            http_response_code(404);
            die("Alert not found");
        }

        header('Content-Type: application/xml; charset=utf-8');
        $capService = new CAPService();
        echo $capService->generateXml($data, $type);
    }
}
