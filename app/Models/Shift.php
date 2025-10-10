<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class Shift extends Model
{
  protected $fillable = [
    'user_id',
    'start_time',
    'end_time',
    'duration',
    'total_amount',
    'total_expense',
  ];

  protected $casts = [
    'start_time' => 'datetime',
    'end_time' => 'datetime',
  ];





  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function actions()
  {
    return $this->hasMany(ShiftAction::class);
  }

  public function getNetProfitAttribute()
  {
    return $this->total_amount - $this->total_expense;
  }

  public function getTotalAmountAttribute()
    {
        return $this->actions()->sum('amount');
    }

    public function getTotalExpenseAttribute()
    {
        return $this->actions()->sum('expense_amount');
    }

    // duration مثال (لو تحفظ start_time و end_time في الشيفت)
    public function getDurationAttribute()
    {
        if (!$this->start_time || !$this->end_time) return null;
        return Carbon::parse($this->end_time)->diffInMinutes(\Carbon\Carbon::parse($this->start_time));
    }



}
