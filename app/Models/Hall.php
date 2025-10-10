<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    protected $fillable = [
        'name',
        'min_capacity',
        'max_capacity',
        'is_active',
        'setter_name'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

