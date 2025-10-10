<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = ["client_id","total_paid"];
  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');

  }
}







