<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPurchase extends Model
{
  protected $fillable = ["booking_id", "product_id", "quantity"];
  public function booking()
  {
    return $this->belongsTo(Booking::class);
  }

  public function product()
  {
    return $this->belongsTo(Product::class);
  }
}
