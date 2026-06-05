<?php
namespace App\Services;

use App\Core\Database;
use DOMDocument;
use DOMXPath;
use RuntimeException;

/**
 * DataSync Service
 * Handles fetching, parsing, and storing disaster data from external sources.
 * It connects to GDACS for floods/fires and SeismicPortal for earthquakes.
 */
class DataSync {
    private $db;

    // The official GDACS RSS feed for major disasters.
    private const GDACS_URL = "https://www.gdacs.org/xml/rss.xml";
    // The SeismicPortal API provides real-time earthquake data in GeoJSON format.
    private const SEISMIC_URL = "https://www.seismicportal.eu/fdsnws/event/1/query?limit=50&format=json";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * This is the main public method that triggers the synchronization for all sources.
     * Aceasta este metoda publică principală care declanșează sincronizarea din toate sursele.
     */
    public function syncExternalData() {
        $this->syncGdacsData();
        $this->syncEarthquakeData();
    }

    /**
     * Fetches and parses RSS data from GDACS (Floods and Fires).
     * This method uses DOMDocument and DOMXPath to correctly parse namespaced XML.
     */
    private function syncGdacsData() {
        // This method gets the data from the GDACS RSS feed.
        try {
            // I'm using DOMDocument to parse the XML from the RSS feed.
            $dom = new DOMDocument();
            if (!$dom->load(self::GDACS_URL)) {
                throw new RuntimeException("Could not connect to GDACS RSS feed. The service might be down.");
            }

            // I'm using DOMXPath to query the XML. It's like SQL for XML.
            $xpath = new DOMXPath($dom);

            // The GDACS feed uses some custom namespaces, so I have to register them here.
            $xpath->registerNamespace('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
            $xpath->registerNamespace('gdacs', 'http://www.gdacs.org');

            // I'm looking for all the <item> tags in the RSS feed.
            $items = $xpath->query("//item");

            // Now I loop through all the items.
            foreach ($items as $news) {
                // I need to get the title, guid, type, lat, long, and date from each item.
                // I'm using the query method from DOMXPath to get the values.
                $title = $xpath->query("title", $news)->item(0)->nodeValue;
                $guid  = $xpath->query("guid", $news)->item(0)->nodeValue;
                $type  = $xpath->query("gdacs:eventtype", $news)->item(0)->nodeValue;
                $lat   = $xpath->query("geo:Point/geo:lat", $news)->item(0)->nodeValue;
                $long  = $xpath->query("geo:Point/geo:long", $news)->item(0)->nodeValue;
                $date  = $xpath->query("pubDate", $news)->item(0)->nodeValue;

                // I only want to save the data if it has a guid, lat, and long.
                if ($guid && $lat !== null && $long !== null) {
                    // I need to convert the date to a format that the database can understand.
                    $dbDate = date('Y-m-d H:i:s', strtotime($date));

                    // I'm checking the type of the disaster and saving it to the correct table.
                    if ($type === 'FL') {
                        $this->saveGdacsToDatabase('floods', $guid, $title, $lat, $long, $dbDate);
                    } elseif ($type === 'WF') {
                        $this->saveGdacsToDatabase('fires', $guid, $title, $lat, $long, $dbDate);
                    }
                }
            }
        } catch (RuntimeException $e) {
            // If something goes wrong, I'm logging the error.
            error_log("GDACS Sync Error: " . $e->getMessage());
        }
    }

    /**
     * Fetches and parses JSON data from SeismicPortal (Earthquakes).
     */
    private function syncEarthquakeData() {
        // This method gets the data from the SeismicPortal API.
        try {
            // using file_get_contents to get the JSON data from the API.
            $jsonContent = file_get_contents(self::SEISMIC_URL);
            if ($jsonContent === false) {
                throw new RuntimeException("Could not connect to SeismicPortal API.");
            }

            // using json_decode to parse the JSON data.
            $data = json_decode($jsonContent, true);
            if (!isset($data['features'])) return;

            // loop through all the features in the JSON data.
            foreach ($data['features'] as $feature) {
                $props = $feature['properties'];
                $geom  = $feature['geometry']['coordinates'];
                $id = $props['unid'] ?? $props['source_id'];

                // check if the earthquake is already in the database.
                $stmt = $this->db->prepare("SELECT * FROM earthquakes WHERE external_id = :id");
                $stmt->execute([':id' => $id]);
                $exists = $stmt->fetch();

                // If the earthquake is not in the database, I insert it.
                if (!$exists) {
                    $sql = "INSERT INTO earthquakes (
                                external_id, magnitude, magnitude_type, latitude, 
                                longitude, depth, region, event_time, source_catalog, authority
                            ) VALUES (:id, :mag, :mag_type, :lat, :lng, :depth, :region, :time, :catalog, :auth)";

                    $stmt = $this->db->prepare($sql);
                    // Note: GeoJSON coordinate order is [longitude, latitude, depth].
                    $stmt->execute([
                        ':id'       => $id,
                        ':mag'      => $props['mag'],
                        ':mag_type' => $props['magtype'],
                        ':lat'      => $geom[1],
                        ':lng'      => $geom[0],
                        ':depth'    => $geom[2] ?? null,
                        ':region'   => $props['flynn_region'],
                        ':time'     => date('Y-m-d H:i:s', strtotime($props['time'])),
                        ':catalog'  => $props['catalog'] ?? null,
                        ':auth'     => $props['auth'] ?? null
                    ]);
                }
            }
        } catch (\Exception $e) {
            // If something goes wrong, I'm logging the error.
            error_log("Earthquake Sync Error: " . $e->getMessage());
        }
    }

    /**
     * Persists a GDACS record (Flood/Fire) using a prepared statement.
     * Uses `ON CONFLICT DO NOTHING` to efficiently ignore duplicate records
     * without needing to run a SELECT check first.
     */
    private function saveGdacsToDatabase($table, $id, $title, $lat, $lng, $time) {
        // We use string interpolation for the table name because PDO cannot bind it.
        // This is safe here because the table name comes from our own code, not user input.
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