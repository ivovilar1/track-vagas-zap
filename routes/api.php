<?php

use App\Http\Controllers\WhatsappMessage\Received;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('webhook/evolution', Received::class)->name('webhook.evolution')->middleware(['evolution.webhook', 'throttle:60,1']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
