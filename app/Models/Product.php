<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
  protected $fillable = ['name', 'price', 'cost', 'quantity'];
  public function purchases()
  {
    return $this->hasMany(SessionPurchase::class);
  }
  public function important_products()
  {
    return $this->hasMany(ImportantProduct::class);
  }
}
