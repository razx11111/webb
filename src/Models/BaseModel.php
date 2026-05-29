<?php

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($limit = 50, $country = null) {
        if ($country) {
            $column = ($this->table === 'earthquakes') ? 'region' : 'title';
            $sql = "SELECT * FROM {$this->table} WHERE {$column} ILIKE :country ORDER BY event_time DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':country', '%' . $country . '%', PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY event_time DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

     // Generic create method to insert data into the table.
    public function create(array $data) {
        // Extracting column names from the associative array
        $columns = implode(', ', array_keys($data));
        
        // Creating named placeholders for the prepared statement (e.g., :magnitude, :latitude)
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        
        // Execution of the statement with the data mapped to the placeholders
        // This automatically handles data escaping to prevent SQL Injection
        return $stmt->execute($data);
    }
}
