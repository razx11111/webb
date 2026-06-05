<?php

namespace App\Models;

/**
 * Earthquake Model
 *
 * Represents the 'earthquakes' table in the database.
 * This class inherits all its functionality from the BaseModel.
 * It only needs to specify the database table it corresponds to.
 */
class Earthquake extends BaseModel {
    // Defines the table name for all queries handled by BaseModel.
    protected $table = 'earthquakes';
}
