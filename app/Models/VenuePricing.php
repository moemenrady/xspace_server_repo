<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenuePricing extends Model
{
    protected $table = 'venue_pricing';

    protected $fillable = [
        'base_hour_price',
        'setter_name',
        'is_active'
    ];
}

