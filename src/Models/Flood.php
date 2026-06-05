<?php

namespace App\Models;

/**
 * Flood Model
 *
 * Represents the 'floods' table in the database.
 * This class inherits all its functionality from the BaseModel.
 * It only needs to specify the database table it corresponds to.
 */
class Flood extends BaseModel {
    // Defines the table name for all queries handled by BaseModel.
    protected $table = 'floods';
}
