<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
  protected $fillable = ['expense_type_id', 'amount', 'note', 'added_by'];
  public function type()
  {
    return $this->belongsTo(ExpenseType::class, 'expense_type_id');
  }
  public function admin()
  {
    return $this->belongsTo(User::class, 'added_by');
  }
}
