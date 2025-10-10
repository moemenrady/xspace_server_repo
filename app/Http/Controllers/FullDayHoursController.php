<?php

namespace App\Http\Controllers;
use App\Models\FullDayHour;
use Illuminate\Http\Request;



class FullDayHoursController extends Controller
{
  public function create()
  {
    $fullDayHours = FullDayHour::orderBy('created_at', 'desc')->get();
    return view('managment.changes.full-day-hours.create
', compact('fullDayHours'));
  }

  public function store(Request $request)
  {
    // ✅ Validation
    $validated = $request->validate([
      'hours_count' => ['required', 'integer', 'min:1'],
      'setter_name' => ['nullable', 'string', 'max:255'],
      'is_active' => ['required', 'boolean'],
    ], [
      'hours_count.required' => '⚠️ عدد الساعات مطلوب.',
      'hours_count.integer' => '⚠️ عدد الساعات يجب أن يكون رقم صحيح.',
      'hours_count.min' => '⚠️ عدد الساعات يجب أن يكون 1 على الأقل.',
      'setter_name.string' => '⚠️ اسم المسجل يجب أن يكون نص.',
      'setter_name.max' => '⚠️ اسم المسجل طويل جدًا.',
      'is_active.required' => '⚠️ حالة التفعيل مطلوبة.',
      'is_active.boolean' => '⚠️ حالة التفعيل غير صحيحة.',
    ]);

    try {
      // إذا المستخدم اختار تفعيل جديد، نوقف كل المفعلين السابقين
      if ($validated['is_active']) {
        FullDayHour::where('is_active', 1)->update(['is_active' => 0]);
      }

      FullDayHour::create($validated);

      return redirect()->back()->with('success', '✅ تم إضافة عدد ساعات جديد لليوم الكامل بنجاح.');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', '❌ حدث خطأ أثناء الإضافة: ' . $e->getMessage());
    }
  }
}








