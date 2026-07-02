<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WidgetController;

Route::get('/widget/init', [WidgetController::class, 'init']);
Route::post('/widget/send-message', [WidgetController::class, 'sendMessage']);