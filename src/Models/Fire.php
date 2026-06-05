<?php

namespace App\Models;

/**
 * Fire Model
 *
 * Represents the 'fires' table in the database.
 * This class inherits all its functionality from the BaseModel.
 * It only needs to specify the database table it corresponds to.
 */
class Fire extends BaseModel {
    // Defines the table name for all queries handled by BaseModel.
    protected $table = 'fires';
}
