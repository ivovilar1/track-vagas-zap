<?php

use App\Http\Controllers\WhatsappMessage\Received;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;



Route::post('webhook/evolution', Received::class)->name('webhook.evolution')->middleware('evolution.webhook');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
