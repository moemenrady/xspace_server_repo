<?php

use App\Http\Controllers\HallController;
Route::middleware('auth')->group(function () {
  Route::get('/bookings/halls/create', [HallController::class, 'create'])->name('bookings.halls.create');
  Route::post('/bookings/halls', [HallController::class, 'store'])->name('halls.store');

});


