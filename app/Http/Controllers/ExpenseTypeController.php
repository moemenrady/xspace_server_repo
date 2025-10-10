<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    /**
     * عرض صفحة create مع أنواع المصروفات - يدعم فلتر الظهور عبر query string
     */
    public function create(Request $request)
    {
        // فلتر الظهور من query string، الافتراضي 'all'
        $appearanceFilter = $request->query('appearance', 'all');

        if ($appearanceFilter === 'admin') {
            // عرض أنواع خاصة بالإدارة فقط (user_appearance = false)
            $records = ExpenseType::where('user_appearance', false)->latest()->get();
        } else {
            // عرض الكل
            $records = ExpenseType::latest()->get();
        }

        return view('managment.changes.expense-types.create', compact('records'));
    }

    /**
     * تخزين نوع مصروف جديد
     */
    public function store(Request $request)
    {
        // ✅ التحقق من المدخلات (مطابق لأسلوب HallController)
        $validated = $request->validate([
            'name'            => 'required|string|max:50',
            'setter_name'     => 'required|string|max:50',
            'user_appearance' => 'required|boolean',
        ]);

        // ✅ الحفظ في قاعدة البيانات
        ExpenseType::create($validated);

        // ✅ رجوع برسالة نجاح
        return redirect()->back()->with('success', 'تم إضافة نوع المصروف بنجاح 🎉');
    }
}
