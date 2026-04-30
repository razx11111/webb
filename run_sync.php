<?php
require_once __DIR__ . '/src/Core/Autoloader.php';
App\Core\Autoloader::register();

use App\Services\DataSync;

echo "Starting synchronization (Earthquakes, Floods, Fires)...\n";
try {
    $sync = new DataSync();
    $sync->syncExternalData();
    echo "Synchronization finished.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
