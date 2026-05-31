<?php

namespace App\Models;

class Admin extends BaseModel {
    protected $table = 'admins';
    protected $orderBy = 'created_at';

    // Admins log in using their phone number or maybe a combination, 
    // but typically a username or specific field is needed. 
    // Based on requirements (nume, prenume, parola, nr_tel), let's use nr_tel as the login identifier.
    public function findByPhone($nr_tel) {
        $sql = "SELECT * FROM {$this->table} WHERE nr_tel = :nr_tel LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nr_tel' => $nr_tel]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
