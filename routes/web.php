<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/store-payment', [PaymentController::class, 'storePayment'])->name('store.payment');
Route::post('/webhook/superwalletz', [PaymentController::class, 'storeSuperWalletzWebhook'])->name('webhook.superwalletz');
