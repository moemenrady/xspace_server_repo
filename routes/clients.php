<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;

Route::middleware('auth')->group(function () {
  // صفحة العملاء
  Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
  Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');

  Route::get('/clients/{id}/barcode', [ClientController::class, 'createBarcode'])->name('clients.barcode');

  Route::get('/search-clients', [ClientController::class, 'search'])->name('clients.search');
  Route::get('/search-clients-id', [ClientController::class, 'searchId'])->name('clients.search.id');
    //تعديل العملاء =======================

  // عرض فورم التعديل
  Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');

  // تحديث بيانات العميل
  Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');


});

