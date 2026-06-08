<?php

namespace App\Controllers;

use App\Models\Flood;
use App\Models\Fire;
use App\Models\Earthquake;
use App\Services\CAPService;
use App\Services\DataSync;

/**
 * CAPController
 * 
 * Handles all logic related to Common Alerting Protocol (CAP) exports.
 * This includes generating RSS feeds with embedded CAP and individual XML alerts.
 */
class CAPController {

    /**
     * @var int The interval in seconds for how often to sync data.
     */
    private const SYNC_INTERVAL = 300; // 5 minute

    /**
     * standard RSS 2.0 feed containing CAP 1.2 alerts.
     * /api/cap?type=all&latest=15
     */
    public function generateCapFeed() {
        $type = $_GET['type'] ?? 'all';
        $limit = isset($_GET['latest']) ? (int)$_GET['latest'] : 15;

        $this->syncData();

        // get disasters with type and data keys
        $disasters = $this->aggregateDisasters($type, $limit);

        // Sort by event time descending
        usort($disasters, function($a, $b) {
            return strtotime($b['data']['event_time']) - strtotime($a['data']['event_time']);
        });

        // Limit the final collection
        if (count($disasters) > $limit) {
            $disasters = array_slice($disasters, 0, $limit);
        }

        $this->renderRssFeed($disasters);
    }

    /**
     * Renders the final RSS feed XML.
     *
     * @param array $disasters The list of disasters to include in the feed.
     */
    private function renderRssFeed(array $disasters) {
        $capService = new CAPService();
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; 

        header('Content-Type: application/rss+xml; charset=utf-8');
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);

        $writer->startElement('rss');
        $writer->writeAttribute('version', '2.0');
        $writer->writeAttributeNS('xmlns', 'cap', null, 'urn:oasis:names:tc:emergency:cap:1.2');

        $writer->startElement('channel');
        $writer->writeElement('title', 'CoA Disaster Alert Feed');
        $writer->writeElement('link', $baseUrl . '/');
        $writer->writeElement('description', 'Latest CAP 1.2 alerts from Crisis Containment Service');

        foreach ($disasters as $item) {
            $data = $item['data'];
            $internalXmlLink = $baseUrl . "/api/cap/alert?type=" . $item['type'] . "&id=" . $data['id'];

            $writer->startElement('item');
            $writer->startElement('title');
            $writer->writeRaw(htmlspecialchars($data['title'] ?? $data['region']));
            $writer->endElement();

            $writer->writeElement('pubDate', date('r', strtotime($data['event_time'])));

            $writer->startElement('link');
            $writer->writeRaw(htmlspecialchars($internalXmlLink));
            $writer->endElement();

            $writer->startElement('guid');
            $writer->writeAttribute('isPermaLink', 'true');
            $writer->writeRaw(htmlspecialchars($internalXmlLink));
            $writer->endElement(); // guid

            // Embed CAP XML directly
            $singleXml = $capService->generateXml($data, $item['type']);
            $cleanXml = preg_replace('/<\?xml[^>]+\?>\s*|<\?xml-stylesheet[^>]+\?>\s*/i', '', $singleXml);
            $writer->writeRaw($cleanXml);

            $writer->endElement(); // item
        }

        $writer->endElement(); // channel
        $writer->endElement(); // rss
        $writer->endDocument();

        echo $writer->outputMemory();
    }

    /**
     * Triggers data synchronization if the last sync was too long ago.
     */
    private function syncData() {
        $syncFile = sys_get_temp_dir() . '/coa_last_sync.txt';
        $currentTime = time();
        $lastSync = file_exists($syncFile) ? (int)file_get_contents($syncFile) : 0;

        if (($currentTime - $lastSync) > self::SYNC_INTERVAL) {
            try {
                $syncService = new DataSync();
                $syncService->syncExternalData();
                file_put_contents($syncFile, $currentTime);
            } catch (\Exception $e) {
                // continue anyways since one failed sync shouldn't break the whole feed
                error_log("Sync failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Aggregates disaster data from various models.
     *
     * @param string $type The type of disaster to fetch ('all', 'flood', 'fire', 'earthquake').
     * @param int $limit The maximum number of records to fetch from each source.
     * @return array The aggregated list of disasters.
     */
    private function aggregateDisasters(string $type, int $limit): array {
        $disasters = [];
        $models = [
            'flood' => new Flood(),
            'fire' => new Fire(),
            'earthquake' => new Earthquake(),
        ];

        $typesToFetch = ($type === 'all') ? array_keys($models) : [$type];

        foreach ($typesToFetch as $disasterType) {
            if (isset($models[$disasterType])) {
                foreach ($models[$disasterType]->getAll($limit) as $record) {
                    $disasters[] = ['type' => $disasterType, 'data' => $record];
                }
            }
        }

        return $disasters;
    }


    /**
     * Endpoint for a single CAP XML message
     * Returns a pure CAP 1.2 XML for a specific record.
     */
    public function exportSingleCap() {
        $type = $_GET['type'] ?? $_GET['amp;type'] ?? '';
        $id = $_GET['id'] ?? $_GET['amp;id'] ?? null;

        if (empty($type) || empty($id)) {
            return $this->sendErrorResponse(400, 'Missing type or id parameters.');
        }

        $model = match ($type) {
            'flood' => new Flood(),
            'fire' => new Fire(),
            'earthquake' => new Earthquake(),
            default => null,
        };

        if (!$model) {
            return $this->sendErrorResponse(400, 'Invalid disaster type specified.');
        }

        $data = $model->getById($id);
        if (!$data) {
            return $this->sendErrorResponse(404, 'The requested alert could not be found.');
        }

        $capService = new CAPService();
        $xml = $capService->generateXml($data, $type);

        $this->sendXmlResponse($xml);
    }

    /**
     * Sends an XML response with appropriate headers.
     *
     * @param string $xml The XML content to output.
     * @param int $statusCode The HTTP status code to send.
     */
    private function sendXmlResponse(string $xml, int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/xml; charset=utf-8');
        echo $xml;
    }

    /**
     * Sends a plain text error response.
     *
     * @param int $statusCode The HTTP status code (e.g., 400, 404, 500).
     * @param string $message The error message to display.
     */
    private function sendErrorResponse(int $statusCode, string $message) {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
}
