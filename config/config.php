<?php

// General Application Settings
define('APP_NAME', 'CoA - Crisis Containment Service');
define('APP_URL', 'http://localhost/webb/public'); // Adjust URL based on local environment

// --- Database Configuration ---
define('DB_HOST', 'postgres');
define('DB_PORT', '5432');
define('DB_NAME', 'webb_db');
define('DB_USER', 'user_webb');
define('DB_PASS', 'password123');


// Start the session automatically for all requests.
session_start();