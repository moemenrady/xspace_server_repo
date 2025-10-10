<?php
use App\Http\Controllers\InvoiceController;

// الاشتراكات 

Route::middleware('auth')->group(function () {
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::get('/clients/{clientId}/invoices', [InvoiceController::class, 'clientInvoices'])->name('client.invoices');


});


