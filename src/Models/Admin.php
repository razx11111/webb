<?php

namespace App\Models;

/**
 * Admin Model
 *
 * Represents the 'admins' table. Extends BaseModel for common database operations
 * and adds a specific method for finding an admin by their phone number,
 * which is used as their unique identifier for login.
 */
class Admin extends BaseModel {
    protected $table = 'admins';
    protected $orderBy = 'created_at';

    /**
     * Finds a single admin by their phone number.
     * @param string $nr_tel The phone number to search for.
     * @return mixed The admin data as an associative array, or false if not found.
     */
    public function findByPhone($nr_tel) {
        $sql = "SELECT * FROM {$this->table} WHERE nr_tel = :nr_tel LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nr_tel' => $nr_tel]);
        return $stmt->fetch();
    }
}
