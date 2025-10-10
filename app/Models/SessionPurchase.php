<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionPurchase extends Model
{
  protected $fillable = ["sation_id", "product_id", "quantity"];
  public function session()
  {
    return $this->belongsTo(Sation::class);
  }

  public function product()
  {
    return $this->belongsTo(Product::class);
  }
}
