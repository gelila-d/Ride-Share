<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trip extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'user_id',
        'driver_id',
        'origin',
        'destination',
        'destination_name',
        'driver_location',
        'is_started',
        'is_completed',
    ];

    /**
     * Cast attributes to proper data types
     */
    protected $casts = [
        'origin' => 'array',
        'destination' => 'array',
        'driver_location' => 'array',
        'is_started' => 'boolean',
        'is_completed' => 'boolean',
    ];

    /**
     * Relationships
     */

    // Trip belongs to a user (rider)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Trip belongs to a driver
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}