<?php

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $orderBy = 'event_time'; // Default ordering column for most tables

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($limit = 50, $country = null) {
        if ($country) {
            $column = ($this->table === 'earthquakes') ? 'region' : 'title';
            $sql = "SELECT * FROM {$this->table} WHERE {$column} ILIKE :country ORDER BY {$this->orderBy} DESC LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':country', '%' . $country . '%', PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM {$this->table} ORDER BY {$this->orderBy} DESC LIMIT :limit";
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
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
