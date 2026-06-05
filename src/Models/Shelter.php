<?php

namespace App\Models;

/**
 * Shelter Model
 *
 * Represents the 'shelters' table in the database.
 * This class is very simple because it inherits all the necessary database
 * functionality (getAll, getById, create) from the BaseModel. We just need
 * to tell it which table to use and how to sort by default.
 */
class Shelter extends BaseModel {
    protected $table = 'shelters';
    protected $orderBy = 'name'; // It makes more sense to order shelters by name
}
