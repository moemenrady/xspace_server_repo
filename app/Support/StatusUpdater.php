<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Shift;
use App\Models\ShiftAction;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class StatusUpdater
{
  public static function run()
  {
    $user = \Auth::user();
    // تحديث الاشتراكات المنتهية
    Subscription::where('is_active', true)
      ->where('end_date', '<=', now()) // كامل التاريخ والوقت
      ->update(['is_active' => false]);

    // تحديث الحجوزات scheduled اللي ميعاد بدايتها جه
    Booking::where('status', 'scheduled')
      ->where('start_at', '<=', now()) // كامل التاريخ والوقت
      ->update(['status' => 'due']);
      
    Subscription::where('is_active', true)
      ->whereNotNull('visit_date')
      ->where('visit_date', '<=', now()->subDay()->toDateString()) // لو عدّى يوم
      ->update(['attendees' => false]);
    Subscription::where('is_active', false)
      ->whereNotNull('visit_date')
      ->update(['attendees' => false]);


    if ($user->hasRole('admin')){
      $now=Carbon::now();
      $todayMidnight = Carbon::today(); // اليوم 00:00 بحسب timezone التطبيق

    // أحدث شيفت مفتوح (إن وجد)
    $openShift = Shift::where('user_id', $user->id)
      ->whereNull('end_time')
      ->latest('start_time')
      ->first();

    // إذا فيه شيفت مفتوح وبدأ قبل منتصف الليل -> نغلقه عند منتصف الليل ونفتح شيفت جديد يبدأ عند منتصف الليل
    if ($openShift) {
      // لو الشيفت بدأ قبل بداية اليوم الحالي => لازم نقسمه (close عند midnight وفتح جديد)
      if (Carbon::parse($openShift->start_time)->lt($todayMidnight)) {
        


          // اغلاق الشيفت القديم عند بداية اليوم (midnight)
          $openShift->end_time = $todayMidnight;
          $openShift->duration = Carbon::parse($openShift->start_time)->diffInMinutes($todayMidnight);
          $openShift->save();

          ShiftAction::create([
            'shift_id' => $openShift->id,
            'action_type' => 'end_shift',
            'notes' => 'تم غلق الشيفت تلقائياً عند بداية اليوم (تقسيم شيفت بدأ قبل منتصف الليل)',
            'amount' => 0,
            'expense_amount' => 0,
          ]);

          // تأكد أنه مفيش شيفت مفتوح تاني بعد الحفظ (حماية من تكرار)
          $exists = Shift::where('user_id', $user->id)->whereNull('end_time')->exists();

          if (!$exists) {
            // نفتح شيفت جديد يبدأ عند منتصف الليل
            $newShift = Shift::create([
              'user_id' => $user->id,
              'start_time' => $todayMidnight,
              'total_amount' => 0,
              'total_expense' => 0,
            ]);

            ShiftAction::create([
              'shift_id' => $newShift->id,
              'action_type' => 'start_shift',
              'notes' => 'تم فتح شيفت تلقائياً بعد تقسيم الشيفت عند بداية اليوم',
              'amount' => 0,
              'expense_amount' => 0,
            ]);
          }


        return;
      }

      // الحالة: فيه شيفت مفتوح وبدأ اليوم نفسه => لا نفعل شي الآن.
      return;
    }    
        $newShift = Shift::create([
          'user_id' => $user->id,
          'start_time' => $todayMidnight,
          'total_amount' => 0,
          'total_expense' => 0,
        ]);

        ShiftAction::create([
          'shift_id' => $newShift->id,
          'action_type' => 'start_shift',
          'notes' => 'تم فتح شيفت تلقائياً لبداية اليوم (start_time = اليوم 00:00)',
          'amount' => 0,
          'expense_amount' => 0,
        ]);
  
    }
  }

}
