<?php

use App\Http\Controllers\Admin\Analytics\AnalyticsController;
use App\Http\Controllers\Admin\Analytics\BookingsAnalyticsController;
use App\Http\Controllers\Admin\Analytics\CustomersAnalyticsController;
use App\Http\Controllers\Admin\Analytics\FinancialAnalyticsController;
use App\Http\Controllers\Admin\Analytics\HallsAnalyticsController;
use App\Http\Controllers\Admin\Analytics\InventoryAnalyticsController;
use App\Http\Controllers\Admin\Analytics\SessionsAnalyticsController;
use App\Http\Controllers\Admin\Analytics\SubscriptionsAnalyticsController;
use App\Http\Controllers\Api\SystemActionController;
use App\Http\Controllers\HallController;

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function(){
  
  Route::prefix('analytics')->name('analytics.')->group(function(){
        Route::get('/', [AnalyticsController::class, 'index'])->name('index'); // صفحة عامة
        Route::get('financial', [FinancialAnalyticsController::class, 'index'])->name('financial');
        Route::get('halls', [HallsAnalyticsController::class, 'index'])->name('halls');
        Route::get('bookings', [BookingsAnalyticsController::class, 'index'])->name('bookings');
        Route::get('subscriptions', [SubscriptionsAnalyticsController::class, 'index'])->name('subscriptions');
        Route::get('sessions', [SessionsAnalyticsController::class, 'index'])->name('sessions');
        Route::get('inventory', [InventoryAnalyticsController::class, 'index'])->name('inventory');
        Route::get('customers', [CustomersAnalyticsController::class, 'index'])->name('customers');
        // API endpoints for charts / filters (used by JS)
        Route::get('api/summary', [AnalyticsController::class, 'summaryApi'])->name('api.summary');
        Route::get('api/halls/top', [HallsAnalyticsController::class, 'topApi'])->name('api.halls.top');
        // ... more API routes as needed
        
    });
});




