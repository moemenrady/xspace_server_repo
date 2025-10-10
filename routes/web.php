<?php

use App\Http\Controllers\Api\ApiSystemActionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SystemActionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; 





Route::middleware('auth')->group(function () {
 Route::get('/api/system-actions', [ApiSystemActionController::class, 'index'])
        ->name('system-actions.web.index')->middleware("admin");
         Route::get('/system-actions', [SystemActionController::class, 'index'])
        ->name('system-actions.index')->middleware("admin");

  Route::post('/products/{id}/add-quantity', [
    ProductController::class,
    'addQuantity'
  ])->name('products.addQuantity');

  Route::get('/dashboard', function () {
    return view('dashboard');
  })->middleware(['auth', 'verified',])->name('dashboard')->middleware("admin");

  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::get('/profile', [ProfileController::class, 'create'])->name('history.index');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  
});

Route::get('/error', function (Request $request) {
  $error = $request->get('message', 'حدث خطأ غير متوقع');
  return view('error.create', compact('error'));
})->name('error.create');

Route::get('/error-system-data', function (Request $request) {
    $error = session('message', 'حدث خطأ غير متوقع'); // يجلب الرسالة من الـ session
    return view('error.admin', compact('error'));
})->name('admin-error.create');

require __DIR__ . '/auth.php';
require __DIR__ . '/products.php';
require __DIR__ . '/bookings.php';
require __DIR__ . '/subscriptions.php';
require __DIR__ . '/managment.php';
require __DIR__ . '/clients.php';
require __DIR__ . '/expenses.php';
require __DIR__ . '/sales.php';
require __DIR__ . '/sessions.php';
require __DIR__ . '/main.php';
require __DIR__ . '/halls.php';
require __DIR__ . '/analytics.php';
require __DIR__ . '/daily.php';
require __DIR__ . '/invoices.php';

