<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AnalyticsService
{
    public function getGeneralSummary(array $options = [])
    {
        return Cache::remember('analytics.general.summary', 60, function () use ($options) {
            $now = Carbon::now();
            $from = $options['from'] ?? $now->copy()->subDays(30);
            $to   = $options['to'] ?? $now;

            $out = [
                'bookings_count'    => 0,
                'estimated_revenue' => 0.0,
                'subscribers'       => 0,
                'low_stock_count'   => 0,
                'period'            => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            ];

            // BOOKINGS
            if (Schema::hasTable('bookings')) {
                try {
                    $out['bookings_count'] = (int) DB::table('bookings')
                        ->whereBetween('start_at', [$from->startOfDay(), $to->endOfDay()])
                        ->count();
                } catch (\Throwable $e) { /* keep zero */ }

                if (Schema::hasColumn('bookings', 'estimated_total')) {
                    try {
                        $out['estimated_revenue'] = (float) DB::table('bookings')
                            ->whereBetween('start_at', [$from->startOfDay(), $to->endOfDay()])
                            ->sum('estimated_total');
                    } catch (\Throwable $e) { /* keep 0 */ }
                }
            }

            // SUBSCRIPTIONS
            if (Schema::hasTable('subscriptions')) {
                try {
                    if (Schema::hasColumn('subscriptions','is_active')) {
                        $out['subscribers'] = (int) DB::table('subscriptions')->where('is_active', true)->count();
                    } else {
                        $out['subscribers'] = (int) DB::table('subscriptions')->count();
                    }
                } catch (\Throwable $e) { /* keep 0 */ }
            }

            // INVENTORY: use products.quantity (your migration) not stock
            if (Schema::hasTable('products')) {
                // prefer min_stock_threshold if exists, otherwise fallback to quantity <= 5
                $hasQuantity = Schema::hasColumn('products','quantity');
                $hasMin = Schema::hasColumn('products','min_stock_threshold');

                if ($hasQuantity) {
                    try {
                        if ($hasMin) {
                            $out['low_stock_count'] = (int) DB::table('products')->whereColumn('quantity','<','min_stock_threshold')->count();
                        } else {
                            $out['low_stock_count'] = (int) DB::table('products')->where('quantity','<=',5)->count();
                        }
                    } catch (\Throwable $e) { /* keep 0 */ }
                }
            }

            return $out;
        });
    }

    public function getShortTrends(string $period = '30d', array $options = [])
    {
        $cacheKey = "analytics.trends.{$period}";
        return Cache::remember($cacheKey, 120, function () use ($period, $options) {
            $now = Carbon::now();
            $days = intval(rtrim($period,'d')) ?: 30;
            $from = $now->copy()->subDays($days);

            $result = [];
            for ($i = 0; $i < $days; $i++) {
                $d = $from->copy()->addDays($i)->toDateString();
                $result[$d] = 0;
            }

            if (!Schema::hasTable('bookings')) return $result;

            try {
                $rows = DB::table('bookings')
                    ->select(DB::raw("DATE(start_at) as day"), DB::raw("count(*) as cnt"))
                    ->whereBetween('start_at', [$from->startOfDay(), $now->endOfDay()])
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get();

                foreach ($rows as $r) {
                    $result[$r->day] = (int) $r->cnt;
                }
            } catch (\Throwable $e) { /* ignore */ }

            return $result;
        });
    }

    public function getTopBookedHalls(int $limit = 5, string $period = '30d')
    {
        $key = "analytics.halls.top.{$limit}.{$period}";
        return Cache::remember($key, 180, function () use ($limit, $period) {
            $now = Carbon::now();
            $days = intval(rtrim($period,'d')) ?: 30;
            $from = $now->copy()->subDays($days);

            $out = [];
            if (!Schema::hasTable('bookings') || !Schema::hasTable('halls')) return $out;

            try {
                $rows = DB::table('bookings')
                    ->select('hall_id', DB::raw('count(*) as cnt'))
                    ->whereBetween('start_at', [$from->startOfDay(), $now->endOfDay()])
                    ->groupBy('hall_id')
                    ->orderByDesc('cnt')
                    ->limit($limit)
                    ->get();

                foreach ($rows as $r) {
                    $hall = DB::table('halls')->where('id', $r->hall_id)->first();
                    $out[] = [
                        'hall_id' => $r->hall_id,
                        'hall_name' => $hall->name ?? ('Hall '.$r->hall_id),
                        'count' => (int) $r->cnt,
                    ];
                }
            } catch (\Throwable $e) { /* ignore */ }

            return $out;
        });
    }

    public function getInventoryAlerts(int $limit = 20)
    {
        if (!Schema::hasTable('products')) return [];

        try {
            if (Schema::hasColumn('products','quantity')) {
                if (Schema::hasColumn('products','min_stock_threshold')) {
                    return DB::table('products')
                        ->select('id','name','quantity','min_stock_threshold')
                        ->whereColumn('quantity','<','min_stock_threshold')
                        ->orderBy('quantity','asc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                } else {
                    return DB::table('products')
                        ->select('id','name','quantity')
                        ->where('quantity','<=',5)
                        ->orderBy('quantity','asc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                }
            }
        } catch (\Throwable $e) { /* ignore */ }

        return [];
    }

    public function getBookingsTimeline($from = null, $to = null)
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to) : Carbon::now();
        $period = $from->diffInDays($to) + 1;

        $result = [];
        for ($i = 0; $i < $period; $i++) {
            $d = $from->copy()->addDays($i)->toDateString();
            $result[$d] = 0;
        }

        if (!Schema::hasTable('bookings')) return $result;

        try {
            $rows = DB::table('bookings')
                ->select(DB::raw("DATE(start_at) as day"), DB::raw("count(*) as cnt"))
                ->whereBetween('start_at', [$from->startOfDay(), $to->endOfDay()])
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            foreach ($rows as $r) {
                $result[$r->day] = (int) $r->cnt;
            }
        } catch (\Throwable $e) { /* ignore */ }

        return $result;
    }
}
