<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    protected $svc;
    public function __construct(AnalyticsService $svc){
        $this->svc = $svc;
    }

    public function index(Request $request){
        try {
            $summary = $this->svc->getGeneralSummary();
            $trends  = $this->svc->getShortTrends('30d');
            $topHalls = $this->svc->getTopBookedHalls(5,'30d');
            $inventoryAlerts = $this->svc->getInventoryAlerts(10);
            $timeline = $this->svc->getBookingsTimeline(null,null);
        } catch (\Throwable $e) {
            Log::warning('Analytics index failed: '.$e->getMessage());
            $summary = ['bookings_count'=>0,'estimated_revenue'=>0,'subscribers'=>0,'low_stock_count'=>0,'period'=>['from'=>null,'to'=>null]];
            $trends = [];
            $topHalls = [];
            $inventoryAlerts = [];
            $timeline = [];
        }

        return view('admin.analytics.index', compact('summary','trends','topHalls','inventoryAlerts','timeline'));
    }

    public function summaryApi(Request $request){
        try {
            $summary = $this->svc->getGeneralSummary();
            $trends  = $this->svc->getShortTrends('30d');
            return response()->json(['summary'=>$summary,'trends'=>$trends]);
        } catch (\Throwable $e) {
            return response()->json(['summary'=>[],'trends'=>[]]);
        }
    }
}
