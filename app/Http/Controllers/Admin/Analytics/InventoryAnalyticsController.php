<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnalyticsService;

class InventoryAnalyticsController extends Controller
{
    protected $svc;
    public function __construct(InventoryAnalyticsController $svc){
        $this->svc = $svc;
    }

    // صفحة التحليلات العامة (1)
    public function index(Request $request){
        // إحضار بيانات سريعة (cached)
        $summary = $this->svc->getGeneralSummary(); // مصفوفة جاهزة للعرض
        $trends = $this->svc->getShortTrends();     // data for sparklines
        return view('admin.analytics.inventory', compact('summary','trends'));
    }

    // API للـ widgets (JSON)
    public function summaryApi(Request $request){
        return response()->json($this->svc->getGeneralSummary());
    }
}


