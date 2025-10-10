<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::middleware('auth')->group(function () {

Route::get('/', function () {

  return view('main.create');
})->middleware('auth')->name('main.create');


});


