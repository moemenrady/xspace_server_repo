<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    protected $fillable = ['name','setter_name', 'user_appearance', ];

    public function drafts()
    {
        return $this->hasMany(ExpenseDraft::class, 'expense_type_id');
    }
    public function expenses()
{
    return $this->hasMany(Expense::class, 'expense_type_id');
}

}