<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductProxyController;

Route::middleware('circuit_breaker')->group(function () {
    Route::get('/proxy/products', [ProductProxyController::class, 'index']);
});


Route::apiResource('users', UserController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
