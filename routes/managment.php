<?php

use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\FullDayHoursController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\ManagmentController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\SystemEditController;
use App\Http\Controllers\VenuePricingController;
Route::middleware(['auth', 'admin'])->group(function () {
  
  Route::get('/managment', [ManagmentController::class, 'create'])->name('managment.create');
  Route::get('/managment/system-edit', [SystemEditController::class, 'create'])->name('managment-system-edit.create');
 Route::get('/managment/halls/create', [HallController::class, 'create'])->name('halls.create');
 Route::get('/managment-base-Hours/create', [VenuePricingController::class, 'create'])->name('records.create');
 Route::post('/managment-base-Hours/create', [VenuePricingController::class, 'store'])->name('records.store');

  Route::get('/managment-expense-types/create', [ExpenseTypeController::class, 'create'])->name('expense-type.create');
 Route::post('/managment-expense-types/create', [ExpenseTypeController::class, 'store'])->name('expense-type.store');

  Route::get('/managment-subscription-plans/create', [SubscriptionPlanController::class, 'create'])->name('subscription-plan.create');
 Route::post('/managment-subscription-plans/create', [SubscriptionPlanController::class, 'store'])->name('subscription-plan.store');

   Route::get('/managment-full-day-hours/create', [FullDayHoursController::class, 'create'])->name('full-day-hours.create');
 Route::post('/managment-full-day-hours/create', [FullDayHoursController::class, 'store'])->name('full-day-hours.store');
});