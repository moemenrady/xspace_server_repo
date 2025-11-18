<?php


Route::middleware(['auth','admin'])->group(function(){
  
Route::prefix('analytics')->name('analytics.')->middleware('auth')->group(function(){
    Route::get('all', [\App\Http\Controllers\AnalyticsController::class,'all'])->name('all');
    Route::get('bookings', [\App\Http\Controllers\AnalyticsController::class,'bookings'])->name('bookings');
    Route::get('clients', [\App\Http\Controllers\AnalyticsController::class,'clients'])->name('clients');
    Route::get('halls', [\App\Http\Controllers\AnalyticsController::class,'halls'])->name('halls');
    Route::get('money', [\App\Http\Controllers\AnalyticsController::class,'money'])->name('money');
    Route::get('plans', [\App\Http\Controllers\AnalyticsController::class,'plans'])->name('plans');
    Route::get('products', [\App\Http\Controllers\AnalyticsController::class,'products'])->name('products');
    Route::get('sessions', [\App\Http\Controllers\AnalyticsController::class,'sessions'])->name('sessions');
    Route::get('subscriptions', [\App\Http\Controllers\AnalyticsController::class,'subscriptions'])->name('subscriptions');
    Route::get('users', [\App\Http\Controllers\AnalyticsController::class,'users'])->name('users');
    Route::get('visits', [\App\Http\Controllers\AnalyticsController::class,'visits'])->name('visits');
  Route::get('total-income', [\App\Http\Controllers\AnalyticsController::class, 'totalIncomeAndProfit'])
    ->name('totalIncomeAndProfit');
  });

});




