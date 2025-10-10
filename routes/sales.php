<?php
//الفواتير 

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SaleProccess;

Route::middleware('auth')->group(function () {

  Route::post('/invoices/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
  Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

  Route::post('/invoices/print', [InvoiceController::class, 'print'])->name('invoices.print');

  //saleProccess 
  Route::get('/sale-proccess/create', [SaleProccess::class, 'create'])->name('sale_proccess.create')->middleware("auth");

  Route::get("/sale-proccess/invoice", [SaleProccess::class, "createInvoice"])->name("invoice.create")->middleware("auth");


});

