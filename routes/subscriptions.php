<?php
use App\Http\Controllers\SubscriptionController;

// الاشتراكات 

Route::middleware('auth')->group(function () {

  // ✅ صفحة الاشتراكات الرئيسية (بالفلاتر و الجدول)
  Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');

  // ✅ صفحة ادارة الاشتراكات (بالفلاتر و الجدول)
  Route::get('/subscriptions-manager', [SubscriptionController::class, 'index_manager'])->name('subscriptions.index-manager');

  // ✅ API Ajax للبحث و الفلترة
  Route::get('/subscriptions/ajax-search', [SubscriptionController::class, 'ajaxSearch'])->name('subscriptions.ajaxSearch');

  // ✅ إنشاء اشتراك جديد
  Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');

  // ✅ عرض تفاصيل اشتراك
  Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');

  // ✅ إنقاص زيارة
  Route::post('/subscriptions/{subscription}/decrease', [SubscriptionController::class, 'decrease'])->name('subscriptions.decrease');

  // ✅ باقي الروتات الخاصة بالخطط والعملاء
  Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
  Route::post('/clients/subscribe', [SubscriptionController::class, 'subscribe'])->name('clients.subscribe');
  Route::post('/clients/{client}/attend', [SubscriptionController::class, 'attend'])->name('clients.attend');

  // routes/web.php
  Route::post('/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])
    ->name('subscriptions.renew');

});


