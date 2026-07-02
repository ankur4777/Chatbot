<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SendMessageRequest;
use App\Services\ChatService;
use App\Services\WidgetService;

class WidgetController extends Controller
{
   protected WidgetService $widgetService;
protected ChatService $chatService;

public function __construct(
    WidgetService $widgetService,
    ChatService $chatService
) {
    $this->widgetService = $widgetService;
    $this->chatService = $chatService;
}
 public function init(Request $request)
{
    return $this->widgetService->initializeWidget($request);
}
public function sendMessage(SendMessageRequest $request)
{
    return $this->chatService->sendMessage($request);
}
}
