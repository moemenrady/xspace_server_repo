<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'education_stage_id');
    }
}
