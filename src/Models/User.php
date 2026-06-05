<?php

namespace App\Models;

/**
 * User Model
 *
 * Represents the 'users' table. It extends the BaseModel to get common functionality
 * and adds its own specific methods for finding users by email or username,
 * which are needed for login and registration checks.
 */
class User extends BaseModel {
    protected $table = 'users';
    protected $orderBy = 'created_at';

    /**
     * Finds a single user by their email address.
     * @param string $email The email address to search for.
     * @return mixed The user data as an associative array, or false if not found.
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Finds a single user by their username.
     * @param string $username The username to search for.
     * @return mixed The user data as an associative array, or false if not found.
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }
}
