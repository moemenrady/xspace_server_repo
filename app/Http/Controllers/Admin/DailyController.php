<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use Carbon\Carbon;

class DailyController extends Controller
{
    public function show(Shift $shift)
    {
        $shift->load('actions','user');
        return view('admin.shifts.show', compact('shift'));
    }

    public function close(Request $request, Shift $shift)
    {
        // close the shift: set end_time to now and compute duration
        if ($shift->end_time) {
            return back()->with('error','الشيفت بالفعل مغلق');
        }

        $shift->end_time = Carbon::now();
        $shift->duration = $shift->end_time->diffInMinutes($shift->start_time);
        // optionally compute totals from actions
        $totalIncome = $shift->actions()->sum('amount');
        $totalExpense = $shift->actions()->sum('expense_amount');
        $shift->total_amount = $totalIncome;
        $shift->total_expense = $totalExpense;
        $shift->save();

        return redirect()->route('admin.calendar.day', $shift->start_time->format('Y-m-d'))
            ->with('success','تم غلق الشيفت بنجاح');
    }
}
