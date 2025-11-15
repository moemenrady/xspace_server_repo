<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
  protected $fillable = [
    'name',
    'phone',
    'is_active',
    'age',
    'specialization_id',
    'education_stage_id',
  ];

  // علاقات
  public function clientSession()
  {
    return $this->hasMany(Sation::class, 'client_id', 'id');
  }

  public function clientVisit()
  {
    return $this->hasMany(Visit::class, 'client_id', 'id');
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class, 'client_id');
  }
  public function subscriptionVisits()
  {
    return $this->hasMany(SubscriptionVisit::class);
  }
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }
  public function bookings()
  {
    return $this->hasMany(Booking::class);
  }

  public function invoices()
  {
    return $this->hasMany(Invoice::class);
  }

  public function activate()
  {
    $this->is_active = 1;
    $this->save();
  }

  public function deactivate()
  {
    $this->is_active = 0;
    $this->save();
  }

  public function specialization()
  {
    return $this->belongsTo(Specialization::class, 'specialization_id');
  }

  public function educationStage()
  {
    return $this->belongsTo(EducationStage::class, 'education_stage_id');
  }
}
