<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FullDayHour extends Model
{
      protected $fillable = [
        'hours_count',
        'setter_name',
        'is_active',
    ];
}
