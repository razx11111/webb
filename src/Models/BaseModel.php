<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Abstract BaseModel
 * Provides a template for all other models (e.g., Flood, Fire).
 * It contains shared logic that all models use, such as connecting to the database
 * and providing basic methods for data retrieval and creation. This approach avoids code repetition.
 */
abstract class BaseModel {
    protected $db;
    protected $table; // This will be set by each child model (e.g., 'floods').
    protected $orderBy = 'event_time'; // Default column to sort by.

    public function __construct() {
        // Get the single database connection instance.
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetches all records from the model's table, with optional filtering and a limit.
     * @param int $limit The maximum number of records to return.
     * @param string|null $country An optional country or region to filter by.
     * @return array An array of records.
     */
    public function getAll($limit = 50, $country = null) {
        // If a country is provided, add a WHERE clause to filter the results.
        if ($country) {
            // Earthquakes are filtered by the 'region' column, while others are filtered by 'title'.
            $column = ($this->table === 'earthquakes') ? 'region' : 'title';
            
            $sql = "SELECT * FROM {$this->table} WHERE {$column} ILIKE :country ORDER BY {$this->orderBy} DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':country', '%' . $country . '%', PDO::PARAM_STR);
        } else {
            // If no country is specified, get all records up to the limit.
            $sql = "SELECT * FROM {$this->table} ORDER BY {$this->orderBy} DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetches a single record by its primary key (id).
     * @param int $id The ID of the record to fetch.
     * @return mixed The record object or false if not found.
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Generic create method to insert a new record into the table.
     * It dynamically builds the query based on the keys of the data array.
     *
     * @param array $data Associative array where keys are column names. ex: ['name' => 'New Shelter', 'capacity' => 100]
     * @return bool True on success, false on failure.
     */
    public function create(array $data) {
        // Get column names from the array keys (e.g., 'name', 'capacity').
        $columns = implode(', ', array_keys($data));
        
        // Create named placeholders for the prepared statement (e.g., ':name', ':capacity').
        $placeholders = ':' . implode(', :', array_keys($data));
        
        // Build the final SQL query.
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        
        // Execute the prepared statement by passing the data array directly.
        // PDO matches the array keys to the named placeholders.
        return $stmt->execute($data);
    }
}
