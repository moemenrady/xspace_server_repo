<?php

use App\Http\Controllers\SessionPurchaseController;
use App\Http\Controllers\SationController;


Route::middleware('auth')->group(function () {


  // Route::get('/session/create', [SationController::class, 'create'])->name("session.create")->middleware("auth");
  Route::post('/sessions/{session}/purchases/update', [SessionPurchaseController::class, 'updatePurchases'])
    ->name('sessionPurchases.update');

  Route::post('/start_session', [SationController::class, 'store'])->name("session.store")->middleware("auth");
  Route::post('/start_session', [SationController::class, 'storeFromManager'])->name("session.store.manager")->middleware("auth");

  Route::get('/sessions', [SationController::class, 'index'])->name('session.index-manager')->middleware("auth");
  Route::put('/sessions/{session}/reduce-minutes', [SationController::class, 'reduceMinutes'])
    ->name('sessions.reduceMinutes');

  Route::get('/sessions/search', [SationController::class, 'search'])->name('sessions.search');

  Route::get('/sessions/{session}', [SationController::class, 'show'])->name('session.show')->middleware("auth");

  Route::get('/sessions/{session}/add-purchases', [SessionPurchaseController::class, 'create'])->name('purchases.create')->middleware("auth");

  Route::post('/sessions/{session}/checkout', [SationController::class, 'checkout'])->name('sessions.checkout');
  Route::put('/sessions/{session}/update-time', [SationController::class, 'updateStartTime'])
    ->name('sessions.updateStartTime');

  // إضافة مشتريات
  Route::post('/sessions/{session}/purchases', [SessionPurchaseController::class, 'storeItem'])->name('session.purchase.store');
  Route::post('/sessions/{session}/items', [SessionPurchaseController::class, 'storeItems'])
    ->name('sessions.storeItems');
  // حذف مشتريات (اختياري)
  Route::delete('/sessions/{session}/purchases/{purchase}', [SessionPurchaseController::class, 'destroy'])->name('purchases.destroy');

  Route::delete('/sessions/{session}/delete-empty', [SationController::class, 'deleteEmpty'])
    ->name('sessions.deleteEmpty')->middleware('auth');
  Route::get('/sessions/search-ajax', [SationController::class, 'searchAjax'])->name('sessions.searchAjax');

  Route::post('/sessions/split', [SationController::class, 'split'])->name('sessions.split');

});

