<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sation extends Model
{
  protected $fillable = [
    'client_id',
    'start_time',
    'persons',
    'end_time',
    'status',
  ];


  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');

  }

  public function purchases() {
    return $this->hasMany(SessionPurchase::class);
}
public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
