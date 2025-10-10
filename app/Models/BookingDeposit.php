<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDeposit extends Model
{
    protected $fillable = [
        'booking_id',
        'invoice_id',
        'amount',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

