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
 * In our SOA (Service-Oriented Architecture), it acts as the glue between
 * the data persistence layer (Models) and the presentation layer (Views/API).
 */
class DisasterController {
    /**
     * Renders the Scholarly HTML Report.
     */
    public function report() {
        // We include the Scholarly site
        require_once __DIR__ . '/../../public/report.html';
    }

    /**
     * Renders the main dashboard page.
     * This is the default entry point for users visiting the site.
     */
    public function index() {
        // We set a title variable that will be used inside the template
        $pageTitle = "Crisis Containment Dashboard";
        
        // Include the home template. Since we're not using a framework,
        // we use standard PHP includes for view rendering.
        require_once __DIR__ . '/../../templates/pages/home.php';
    }

    /**
     * API Endpoint: Fetch latest disasters
     * 
     * Returns a JSON object containing both floods and fires.
     * Used by the frontend Fetch API to update the UI without reloading.
     */
    public function getDisasters() {
        // Set the response header to JSON for API compatibility
        header('Content-Type: application/json');
        
        try {
            // Instantiate models to interact with the PostgreSQL database
            $floodModel = new Flood();
            $fireModel = new Fire();
            $earthquakeModel = new Earthquake();

            // Aggregate data from all sources
            $data = [
                'floods'      => $floodModel->getAll(20),
                'fires'       => $fireModel->getAll(20),
                'earthquakes' => $earthquakeModel->getAll(20)
            ];

            // Send the JSON response
            echo json_encode($data);
        } catch (\Exception $e) {
            // In case of database or logic errors, return a 500 status code
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch disaster data: ' . $e->getMessage()]);
        }
    }

    /**
     * Sync Endpoint: Triggers the GDACS synchronization
     * 
     * This method invokes the DataSync service to fetch fresh data from RSS.
     * It's designed to be called via an AJAX request from the admin/dashboard.
     */
    public function sync() {
        header('Content-Type: application/json');
        try {
            // The DataSync service handles the heavy lifting of XML parsing
            $syncService = new DataSync();
            $syncService->syncExternalData();
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Data synchronization complete. Tables updated.'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }
    // Go to floods page
    public function getFloods()
    {
        $pageTitle = "Floods Management";
        require_once __DIR__ . '/../../templates/pages/floods.php';
    }
    // Go to earthquakes page
    public function getEarthquakes()
    {
        $pageTitle = "Earthquakes Management";
        require_once __DIR__ . '/../../templates/pages/earthquakes.php';
    }
    // Go to fires page
    public function getFires()
    {
        $pageTitle = "Fires Management";
        require_once __DIR__ . '/../../templates/pages/fires.php';
    }
}