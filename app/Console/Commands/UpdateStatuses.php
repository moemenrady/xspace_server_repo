<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Booking;

class UpdateStatuses extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'statuses:update';

    /**
     * The console command description.
     */
    protected $description = 'تحديث حالة الاشتراكات والحجوزات حسب الوقت الحالي';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // ✅ تحديث الاشتراكات
        $updatedSubscriptions = Subscription::where('end_date', '<', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // ✅ تحديث الحجوزات
        $updatedBookings = Booking::where('start_at', '<=', now())
            ->where('status', 'scheduled')
            ->update(['status' => 'due']);

        $this->info("updated $updatedSubscriptions and subsciption $updatedBookings booking.");
    }
}
