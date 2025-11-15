<?php
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionVisitController;

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
  // sub visites
  Route::post('/subscription-visits', [SubscriptionVisitController::class, 'store']);



  Route::get('subscriptions/{subscription}/visits', [SubscriptionVisitController::class, 'showVisits'])
    ->name('subscriptions.visits.show')
    ->middleware('auth');

// API لجلب زيارات الاشتراك (AJAX)
Route::get('subscriptions/{subscription}/visits/list', [SubscriptionVisitController::class, 'visitsList'])
    ->name('subscriptions.visits.list')
    ->middleware('auth');

// ختم الخروج للزيارة
Route::post('subscription-visits/{visit}/checkout', [SubscriptionVisitController::class, 'checkout'])
    ->name('subscription-visits.checkout')
    ->middleware('auth');
});


