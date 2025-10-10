<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
  protected $fillable = [
    'name',
    'visits_count',
    'duration_days',
    'price',
    'setter_name'
  ];

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class, 'plan_id');
  }
}

