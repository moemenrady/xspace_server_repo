<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingPurchaseController;


Route::middleware('auth', )->group(function () {
      Route::post('/bookings/{booking}/purchases/update', [BookingPurchaseController::class, 'updatePurchases'])
    ->name('booking.purchases.update');
  Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
    ->name('bookings.cancel');
Route::post('bookings/check-conflict', [BookingController::class, 'checkConflict'])
    ->name('bookings.check_conflict');
  Route::get('/bookings/by-date', [BookingController::class, 'byDate'])->name('bookings.byDate');

// جلب تقديري للسعر (مستخدم من الواجهة)
Route::get('bookings/estimate', [BookingController::class, 'estimate'])
    ->name('bookings.estimate');

  Route::get('/bookings/ajax-search', [BookingController::class, 'ajaxSearch'])->name('bookings.ajaxSearch');
  Route::get('/bookings/ajax-search-manager', [BookingController::class, 'ajaxSearchManager'])->name('bookings.ajaxSearchManager');

  Route::get('/bookings/search', [BookingController::class, 'search'])->name('bookings.search');
// web.php
  // // الصفحة الرئيسية لعرض كل الحجوزات (index)
  Route::get('/bookings-manager', [BookingController::class, 'index_manager'])->name('bookings.index-manager');

  Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');

  // صفحة إضافة حجز جديد (form)
  Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
  Route::delete('/bookings/{booking}/purchases', [BookingPurchaseController::class, 'storeItem'])->name('bookings.destroy');

  // حفظ الحجز الجديد (store)
  Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('/start-booking', [BookingController::class, 'startBookingNow'])->name('bookings.start-now');

  Route::get('/bookings/check-ongoing', [BookingController::class, 'checkOngoing'])->name('bookings.check_ongoing');


  // // تعديل حجز موجود
  
  Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
// مكان مناسب ضمن الـ group الخاص بالـ bookings
Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');

  Route::get('/bookings/same-day', [BookingController::class, 'sameDay'])
    ->name('bookings.sameDay');

  Route::get('/bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
  Route::get('/bookings/{id}', [BookingController::class, 'show'])->name("bookings.show");
  Route::get('/client/{id}/bookings', [BookingController::class, 'clientBookings'])->name("client.bookings");

  
  Route::post('/bookings/{booking}/start', [BookingController::class, 'start'])->name('bookings.start');
  Route::post('/bookings/{booking}/end', [BookingController::class, 'checkout'])->name('booking.checkout');
  Route::get('/bookings/{booking}/add-purchases', [BookingPurchaseController::class, 'create'])->name('booking.purchases.create');
  Route::post('/bookings/{booking}/purchases', [BookingPurchaseController::class, 'storeItem'])->name('booking.purchase.store');



});

