<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Manages the database connection using the Singleton pattern.
 * This ensures that there is only one connection to the database per request, which is efficient.
 * Credentials are loaded from the central config file, not stored here.
 */
class Database
{
    private static $instance = null;
    private $conn = null;

    /**
     * The constructor is private to prevent creating new instances directly.
     * It establishes the database connection using settings from the config file.
     */
    private function __construct() {
        // Build the DSN (Data Source Name) string from the config constants.
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

        try {
            // Create the PDO connection instance.
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);

            // Set PDO error mode to exception. This is a good practice.
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Set the default fetch mode to associative array.
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $exception) {
            // If the connection fails, stop the application and show an error.
            die("Database Connection Error: " . $exception->getMessage());
        }
    }

    /**
     * The static method that controls the access to the single instance.
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Returns the active PDO connection object.
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Prevents cloning the instance, which maintains the Singleton pattern.
     */
    private function __clone() {}
}