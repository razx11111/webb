<?php

namespace App\Models;

/**
 * Shelter Model
 * 
 * Handles storage and retrieval of safe shelter locations.
 */
class Shelter extends BaseModel {
    protected $table = 'shelters';
    protected $orderBy = 'created_at';

    /**
     * Persists a new shelter location.
     */
    public function create($data) {
        $sql = "INSERT INTO shelters (name, latitude, longitude, capacity) 
                VALUES (:name, :lat, :lng, :cap)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':lat'  => $data['latitude'],
            ':lng'  => $data['longitude'],
            ':cap'  => $data['capacity'] ?? null
        ]);
    }
}
