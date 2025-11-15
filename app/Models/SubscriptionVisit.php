<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SubscriptionVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'client_id',
        'visit_number',
        'checked_in_at',
        'checked_out_at',
        'duration_minutes',
        'attended',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'attended' => 'boolean',
    ];

    // العلاقات
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessor لحساب المدة لو مش مخزنة
    public function getComputedDurationMinutesAttribute()
    {
        if ($this->duration_minutes) {
            return $this->duration_minutes;
        }
        if ($this->checked_in_at && $this->checked_out_at) {
            return $this->checked_out_at->diffInMinutes($this->checked_in_at);
        }
        return null;
    }
}
