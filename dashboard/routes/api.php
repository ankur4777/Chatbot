<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\Api\KnowledgeController;
Route::get('/widget/init', [WidgetController::class, 'init']);
Route::post('/widget/send-message', [WidgetController::class, 'sendMessage']);
Route::post('/widget/flow-answer', [WidgetController::class, 'saveFlowAnswer']);
Route::post('/widget/end-chat', [WidgetController::class, 'endChat']);
Route::get('/widget/flow', [WidgetController::class, 'flow']);
Route::get('/websites/{website}/knowledge',[KnowledgeController::class, 'knowledge']);

Route::get(
    '/websites/{website}/chunks',
    [KnowledgeController::class, 'chunks']
);

Route::post(
    '/widget/save-lead',
    [WidgetController::class, 'saveLead']
);