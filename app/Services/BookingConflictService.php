<?php
namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class BookingConflictService
{
    
  public function hasConflict(int $hallId, Carbon $start, Carbon $end): bool
{
    return Booking::where('hall_id', $hallId)
        ->whereIn('status', ['scheduled', 'due', 'in_progress']) // ✅ نتحقق بس من الحالات الفعّالة
        ->where(function($q) use ($start, $end) {
            $q->where('start_at', '<', $end)
              ->where('end_at', '>', $start);
        })
        ->exists();
}

}
