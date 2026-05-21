<?php

namespace App\Core;

use PDO;
use PDOException;

// Makes the connection with PostgreSQL database using PDO
// Using the Singleton model for not permitting the creation of multiple connections with the database.
class Database
{
    private static $instance = null;
    private $host = "postgres";
    private $port = "5432";
    private $db_name = "webb_db";
    private $username = "user_webb";
    private $password = "password123";
    private $conn = null;

    // The Constructor of the class
    public function __construct() {
        $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;

        try {
            // Start the connection
            $this->conn = new PDO($dsn, $this->username, $this->password);
            // Throw a clear exception in case of an error
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set the format of received information like an array (ex:['magnitude' => 5.2, 'region' => 'Vrancea'])
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $exception) {
            // O aruncăm pentru a fi prinsă în Controller.
            throw new \Exception("Database Connection Error: " . $exception->getMessage());
        }
    }

    // Static method for returning one instance
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Return the PDO object for interrogations
    public function getConnection() {
        return $this->conn;
    }

    // Don't allow others to use clone, this way preserving the Singleton Model
    private function __clone() {}

}