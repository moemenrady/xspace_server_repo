<?php
  
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;




Route::middleware('auth')->group(function () {

//  عرض صفحة الاضافه منتج جديد
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');

// تخزين المنتج في db
Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');

// عرض صفحة كمية منتج موجود 
Route::get('/products/add-quantity-page', [ProductController::class, 'addQuantityPage'])->name('products.addQuantityPage');

// تخزين كمية منتج موجود في db
Route::post('/products/{id}/add-quantity', [ProductController::class, 'addQuantity'])->name('products.addQuantity');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

// عرض جميع المنتجات
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// البحث عن المنتجات الموجوده


Route::get('/important-products/create', [ProductController::class, 'createImportant'])->name('important-products.create');


Route::post('/important-products', [ProductController::class, 'storeImportant'])
    ->name('important_products.store');
Route::put('/important-products/{importantProduct}', [ProductController::class, 'updateImportant'])
    ->name('important_products.update');

});


