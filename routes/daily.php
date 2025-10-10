<?php

use App\Http\Controllers\Admin\ShiftAdminController;
use App\Http\Controllers\ShiftController;

Route::middleware('auth')->group(function () {

  Route::get('/shift-manager', [ShiftController::class, 'create'])->name('shift.create');
  Route::get('/shifts', [ShiftController::class, 'index'])->name('shift.index');
  
  Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shift.show');
  Route::post('/shift/start', [ShiftController::class, 'startShift'])->name('shift.start');
    Route::get('/shift/prompt', [ShiftController::class, 'prompt'])->name('shift.prompt');
  Route::post('/shift/end', [ShiftController::class, 'endShift'])->name('shift.end');
  Route::get('/shift/check-open', [ShiftController::class, 'checkOpen'])->name('shift.check');

// صفحة الكالندر (اختيار اليوم)
Route::get('/admin/calendar', [ShiftAdminController::class, 'calendar'])->name('admin.calendar')->middleware("admin");

// صفحة شيفتات اليوم (موجودة عندك سابقاً، تأكد من اسم الroute إذا مختلف)
Route::get('/admin/day-shifts', [ShiftAdminController::class, 'dayShifts'])->name('admin.day_shifts')->middleware("admin");

});

// Calendar & day view
Route::middleware(['auth', 'admin'])->group(function () {
});



