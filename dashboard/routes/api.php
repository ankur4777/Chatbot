<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WidgetController;
use App\Http\Controllers\Api\KnowledgeController;
Route::get('/widget/init', [WidgetController::class, 'init'])
    ->middleware('throttle:widget-init');
Route::post('/widget/send-message', [WidgetController::class, 'sendMessage'])
    ->middleware('throttle:widget-message');
Route::post('/widget/request-live-chat', [WidgetController::class, 'requestLiveChat'])
    ->middleware('throttle:widget-live-chat');
Route::post('/widget/offline-live-chat-request', [WidgetController::class, 'saveOfflineLiveChatRequest'])
    ->middleware('throttle:widget-live-chat');
Route::post('/widget/live-chat-typing', [WidgetController::class, 'liveChatTyping'])
    ->middleware('throttle:widget-realtime');
Route::post('/widget/realtime-auth', [WidgetController::class, 'realtimeAuth'])
    ->middleware('throttle:widget-realtime');
Route::post('/widget/conversation-state', [WidgetController::class, 'conversationState'])
    ->middleware('throttle:widget-realtime');
Route::post('/widget/live-chat-rating/pending', [WidgetController::class, 'pendingLiveChatRating'])
    ->middleware('throttle:widget-realtime');
Route::post('/widget/live-chat-rating/submit', [WidgetController::class, 'submitLiveChatRating'])
    ->middleware('throttle:widget-live-chat');
Route::post('/widget/live-chat-rating/skip', [WidgetController::class, 'skipLiveChatRating'])
    ->middleware('throttle:widget-live-chat');
Route::get('/widget/attachments/{message}', [WidgetController::class, 'showAttachment'])
    ->middleware('throttle:widget-realtime');
Route::post('/widget/flow-answer', [WidgetController::class, 'saveFlowAnswer'])
    ->middleware('throttle:widget-message');
Route::post('/widget/end-chat', [WidgetController::class, 'endChat'])
    ->middleware('throttle:widget-live-chat');
Route::get('/widget/flow', [WidgetController::class, 'flow'])
    ->middleware('throttle:widget-init');
Route::get('/websites/{website}/knowledge',[KnowledgeController::class, 'knowledge']);

Route::get(
    '/websites/{website}/chunks',
    [KnowledgeController::class, 'chunks']
);

Route::post(
    '/widget/save-lead',
    [WidgetController::class, 'saveLead']
);
