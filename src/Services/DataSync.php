<?php
namespace App\Services;

use App\Core\Database; // Singleton database connection from webb_2.zip
use DOMDocument;
use DOMXPath;
use RuntimeException;

/**
 * DataSync Service
 * Handles fetching, parsing, and storing disaster data from GDACS RSS feeds.
 */
class DataSync {
    private $db;
    // The official GDACS RSS feed URL
    private const GDACS_URL = "https://www.gdacs.org/xml/rss.xml";
    // SeismicPortal API for real-time earthquake data (last 50 events)
    private const SEISMIC_URL = "https://www.seismicportal.eu/fdsnws/event/1/query?limit=50&format=json";

    public function __construct() {
        // Obtain the PDO connection from the Core Database class
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Synchronizes external disaster data with the local PostgreSQL database.
     */
    public function syncExternalData() {
        $this->syncGdacsData();
        $this->syncEarthquakeData();
    }

    /**
     * Fetches and parses RSS data from GDACS (Floods and Fires)
     */
    private function syncGdacsData() {
        try {
            $dom = new DOMDocument();
            
            // Load the remote XML content safely
            if (!@$dom->load(self::GDACS_URL)) {
                throw new RuntimeException("Could not connect to GDACS RSS feed.");
            }

            $xpath = new DOMXPath($dom);
            
            // Register namespaces found in the GDACS XML header to allow XPath queries
            $xpath->registerNamespace('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
            $xpath->registerNamespace('gdacs', 'http://www.gdacs.org');

            // Find all <item> tags in the RSS feed
            $items = $xpath->query("//item");

            foreach ($items as $news) {
                // Use a helper method to safely extract node values and avoid "null" errors
                $title = $this->getNodeValue($xpath, "title", $news);
                $guid  = $this->getNodeValue($xpath, "guid", $news);
                $type  = $this->getNodeValue($xpath, "gdacs:eventtype", $news);
                $lat   = $this->getNodeValue($xpath, "geo:Point/geo:lat", $news);
                $long  = $this->getNodeValue($xpath, "geo:Point/geo:long", $news);
                $date  = $this->getNodeValue($xpath, "pubDate", $news);

                // Validation: Only proceed if we have the mandatory ID and coordinates
                if ($guid && $lat !== null && $long !== null) {
                    // Convert RSS date to PostgreSQL compatible format
                    $dbDate = date('Y-m-d H:i:s', strtotime($date));

                    // Route data based on disaster type codes (FL = Flood, WF = Wildfire)
                    if ($type === 'FL') {
                        $this->saveGdacsToDatabase('floods', $guid, $title, $lat, $long, $dbDate);
                    } elseif ($type === 'WF') {
                        $this->saveGdacsToDatabase('fires', $guid, $title, $lat, $long, $dbDate);
                    }
                }
            }
        } catch (RuntimeException $e) {
            error_log("GDACS Sync Error: " . $e->getMessage());
        }
    }

    /**
     * Fetches and parses JSON data from SeismicPortal (Earthquakes)
     */
    private function syncEarthquakeData() {
        try {
            // Fetch JSON content from SeismicPortal
            $jsonContent = @file_get_contents(self::SEISMIC_URL);
            if (!$jsonContent) {
                throw new RuntimeException("Could not connect to SeismicPortal.");
            }

            $data = json_decode($jsonContent, true);
            if (!isset($data['features'])) return;

            foreach ($data['features'] as $feature) {
                $props = $feature['properties'];
                $geom  = $feature['geometry']['coordinates'];

                $sql = "INSERT INTO earthquakes (
                            external_id, magnitude, magnitude_type, latitude, 
                            longitude, depth, region, event_time, source_catalog, authority
                        ) VALUES (:id, :mag, :mag_type, :lat, :lng, :depth, :region, :time, :catalog, :auth)
                        ON CONFLICT (external_id) DO NOTHING";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':id'       => $props['unid'] ?? $props['source_id'],
                    ':mag'      => $props['mag'],
                    ':mag_type' => $props['magtype'],
                    ':lat'      => $geom[1], // Latitude is second in GeoJSON coordinates [lng, lat, depth]
                    ':lng'      => $geom[0],
                    ':depth'    => $geom[2] ?? null,
                    ':region'   => $props['flynn_region'],
                    ':time'     => date('Y-m-d H:i:s', strtotime($props['time'])),
                    ':catalog'  => $props['catalog'] ?? null,
                    ':auth'     => $props['auth'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            error_log("Earthquake Sync Error: " . $e->getMessage());
        }
    }

    /**
     * Helper method to safely extract a node's value.
     */
    private function getNodeValue($xpath, $query, $context) {
        $nodeList = $xpath->query($query, $context);
        if ($nodeList && $nodeList->length > 0) {
            return $nodeList->item(0)->nodeValue;
        }
        return null;
    }

    /**
     * Persists a GDACS record (Flood/Fire) using PDO Prepared Statements.
     */
    private function saveGdacsToDatabase($table, $id, $title, $lat, $lng, $time) {
        $sql = "INSERT INTO $table (external_id, title, latitude, longitude, event_time) 
                VALUES (:id, :title, :lat, :lng, :time) 
                ON CONFLICT (external_id) DO NOTHING";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'    => $id,
                ':title' => $title,
                ':lat'   => $lat,
                ':lng'   => $lng,
                ':time'  => $time
            ]);
        } catch (\PDOException $e) {
            error_log("Database Insert Error in $table: " . $e->getMessage());
        }
    }
}