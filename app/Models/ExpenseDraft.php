<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseDraft extends Model
{
    use HasFactory;

    protected $fillable = [
         'note',
        'estimated_amount',
        'expense_type_id',
        'created_by',
    ];

    // علاقة مع جدول الأنواع
    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }

    // علاقة مع المستخدم (اللي أنشأ الدرفت)
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }



    // العلاقة العكسية: شيفت أكشنز اللي مرتبطة بالدرافت
    public function shiftActions()
    {
        return $this->hasMany(ShiftAction::class, 'expense_draft_id');
    }
}


