<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientController;

Route::middleware('auth')->group(function () {
  // صفحة العملاء
  Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
  Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');

  Route::get('/clients/{id}/barcode', [ClientController::class, 'createBarcode'])->name('clients.barcode');

  Route::get('/search-clients', [ClientController::class, 'search'])->name('clients.search');

});

