<?php

// app/Models/InvoiceItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
  protected $table = 'invoice_items';
  protected $fillable = [
        'invoice_id','item_type','product_id','subscription_id','booking_id','session_id',
        'name','qty','price','cost','total','description'
    ];
 protected $guarded = [];
    public function invoice(): BelongsTo {
        return $this->belongsTo(Invoice::class);
    }
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
    public function deposite(): BelongsTo {
        return $this->belongsTo(BookingDeposit::class);
    }
      public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }
}
