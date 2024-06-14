<?php

use Illuminate\Support\Facades\Route;

Route::get('/products', [App\Http\Controllers\ProductProxyController::class, 'index']);


Route::get('/', function () {
    return view('welcome');
});
