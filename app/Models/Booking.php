<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    // app/Models/Booking.php
protected $fillable = [
    'hall_id',
    'client_id',
    'title',
    'attendees',
    'start_at',
    'duration_minutes',
    'end_at',
    'real_start_at',
    'real_end_at',
    'status',
    'base_hour_price',
    'extra_person_hour_price',
    'min_capacity_snapshot',
    'estimated_total',
    'real_total', // <-- صححت الاسم ليطابق migration
];




    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'real_start_at' => 'datetime', // ✅ لازم نفس اسم العمود
        'real_end_at' => 'datetime',   // ✅ لازم نفس اسم العمود
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function deposits()
    {
        return $this->hasMany(BookingDeposit::class,);
    }

    public function purchases()
    {
        return $this->hasMany(BookingPurchase::class);
    }
public function invoice()
{
    return $this->hasOne(Invoice::class, 'booking_id');
}
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function scopeOverlaps($q, $start, $end)
    {
        return $q->where(function ($query) use ($start, $end) {
            $query->whereBetween('start_at', [$start, $end])
                ->orWhereBetween('end_at', [$start, $end])
                ->orWhere(function ($sub) use ($start, $end) {
                    $sub->where('start_at', '<', $start)
                        ->where('end_at', '>', $end);
                });
        });
    }
    // app/Models/Booking.php
public function scopeActiveStatuses($query)
{
    return $query->whereIn('status', ['scheduled', 'due', 'in_progress']);
}

}
