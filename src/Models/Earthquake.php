<?php

namespace App\Models;

/**
 * Earthquake Model
 * 
 * Simple model for interacting with the 'earthquakes' table.
 */
class Earthquake extends BaseModel {
    // Defines the table name used by BaseModel methods
    protected $table = 'earthquakes';
}
