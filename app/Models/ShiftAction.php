<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftAction extends Model
{
    protected $fillable = [
        'shift_id',
        'action_type',
        'invoice_id',
        'amount',
        'expense_amount',
        'expense_draft_id', // تأكد أنه موجود هنا
        'notes',
    ];
     protected $table = 'shift_actions';
    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class,'invoice_id');
    }

    // الاسم الصحيح للعلاقة
    public function expenseDraft()
    {
        return $this->belongsTo(ExpenseDraft::class, 'expense_draft_id');
    }
}
