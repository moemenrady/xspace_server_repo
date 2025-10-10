<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
  protected $fillable = [
    'client_id',
    'plan_id',
    'remaining_visits',
    'start_date',
    'end_date',
    'is_active',
    'renewal_count',
    'visit_date',
    'attendees'
  ];
  protected $casts = [
    'start_date' => 'datetime',
    'end_date' => 'datetime',
    'visit_date'=>'datetime'
  ];

  public function client()
  {
    return $this->belongsTo(Client::class);
  }

  public function plan()
  {
    return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
  }
}
