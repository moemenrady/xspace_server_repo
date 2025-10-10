<?php
// app/Models/Invoice.php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{

  protected $fillable = [
    'invoice_number',
    'client_id',
    'booking_id',
    'type',
    'subtotal',
    'discount_type',
    'discount_value',
    'discount_amount',
    'discount_reason',
    'discount_by',
    'total',
    'profit',
    'notes'
  ];

  protected $casts = [
    'subtotal' => 'decimal:2',
    'discount_value' => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'total' => 'decimal:2',
    'profit' => 'decimal:2',
  ];


   protected $guarded = [];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }


public function booking()
{
    return $this->belongsTo(Booking::class, 'booking_id');
}

  public function client()
  {
    return $this->belongsTo(Client::class);
  }
  public function user()
  {
    return $this->belongsTo(User::class, 'client_id');

  }
  public function deposits()
  {
    return $this->hasMany(BookingDeposit::class);
  }

  public function scopeBetween($query, $from, $to)
  {
    return $query->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()]);
  }
    public function shiftActions()
    {
        return $this->hasMany(ShiftAction::class, 'invoice_id');
    }
}
