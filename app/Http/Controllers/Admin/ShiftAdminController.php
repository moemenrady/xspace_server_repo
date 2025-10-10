<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use Carbon\Carbon;

class ShiftAdminController extends Controller
{

public function dayShifts(Request $request)
{
    // خذ التاريخ من query param أو استخدم تاريخ اليوم (YYYY-MM-DD)
    $date = $request->query('date')
        ? Carbon::parse($request->query('date'))->toDateString()
        : Carbon::today()->toDateString();

    // نجيب جميع الشيفتات التي أنشئت في هذا اليوم (created_at date)
    // مع user عشان العرض
    $shifts = Shift::with('user')
        ->whereDate('created_at', $date)
        ->orderBy('created_at')
        ->get();

    // لكل شيفت: إذا لم يكن هناك قيمة duration مخزنة، وحقل updated_at موجود
    // نحسب المدة بالـ دقائق (مؤقتًا في الكائن) لكي يعرض الـ Blade نفس الشكل كالـ index.
    foreach ($shifts as $shift) {
        // نعتبر الشيفت مُغلقاً إذا كان updated_at مختلفًا عن created_at أو لو عندك عمود end_time غير null
        $isClosed = !is_null($shift->updated_at) && $shift->updated_at->gt($shift->created_at);

        // إذا لم يُخزن duration مسبقًا، وحصلنا على end (updated_at) — نحسب دقائق
        if (empty($shift->duration) && $isClosed) {
            // حساب الدقائق بين البداية والنهاية
            $minutes = $shift->created_at->diffInMinutes($shift->updated_at);
            // ضع القيمة في الخاصية المؤقتة duration حتى يستخدمها الـ Blade كما هو متوقع
            $shift->duration = $minutes;
        }

        // لو عايز تعرض end_time صراحة وتستخدمها في الـ view، يمكن تعريف خاصية مساعدة:
        $shift->computed_end_time = $isClosed ? $shift->updated_at->format('Y-m-d H:i') : null;
        $shift->computed_start_time = $shift->created_at ? $shift->created_at->format('Y-m-d H:i') : null;
    }

    // المجاميع (نجمع على كل الشيفتات الراجعة — لو تريد المجاميع للشيفتات المغلقة فقط غيّر where)
    $total_income = $shifts->sum(fn($s) => $s->total_amount ?? 0);
    $total_expense = $shifts->sum(fn($s) => $s->total_expense ?? 0);
    $total_net = $total_income - $total_expense;

    return view('daily.admin.day_shifts', compact(
        'date',
        'shifts',
        'total_income',
        'total_expense',
        'total_net'
    ));
}
        public function calendar()
    {
        // هذه الصفحة لا تحتاج بيانات مسبقة؛ الJS سيجلب بيانات الـ bookings من route موجود عندك: bookings.calendar
        return view('daily.admin.create');
    }
}
