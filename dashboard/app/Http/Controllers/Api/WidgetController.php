<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SendMessageRequest;
use App\Services\ChatService;
use App\Services\WidgetService;
use App\Models\ChatConversation;

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
    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

   $this->widgetService->recordVisitorActivity(
    $request,
    $website
);

    return $this->chatService->sendMessage($request);
}

public function endChat(Request $request)
{
    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    if (! $request->conversation_id) {
        return response()->json([
            'success' => true,
            'message' => 'No active conversation.',
        ]);
    }

    $conversation = ChatConversation::where('id', $request->conversation_id)
        ->where('website_id', $website->id)
        ->where('status', 'active')
        ->first();


    if ($conversation) {
    $this->chatService->endConversation($conversation);

   $this->widgetService->recordVisitorActivity(
    $request,
    $website
);
}

    return response()->json([
        'success' => true,
        'message' => 'Conversation ended successfully.',
    ]);
}
public function flow(Request $request)
{
    return $this->widgetService->getFlow($request);
}

public function saveFlowAnswer(Request $request)
{
    $request->validate([
        'widget_key' => 'required',
        'session_id' => 'required',
        'chatbot_flow_step_id' => 'required|integer',
        'answer' => 'required|string',
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    $this->widgetService->recordVisitorActivity(
    $request,
    $website
);

    $result = $this->chatService->saveFlowAnswer(
        $request,
        $website
    );

    return response()->json([
        'success' => true,
        'conversation_id' => $result['conversation']->id,
        'answer_id' => $result['answer']->id,
    ]);
}
public function saveLead(Request $request)
{
    $request->validate([
        'widget_key' => 'required',
        'session_id' => 'required',
        'conversation_id' => 'required|integer',
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'notes' => 'nullable|string',
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    $lead = $this->chatService->saveLeadData(
        $request,
        $website
    );

    $this->widgetService->recordVisitorActivity(
        $request,
        $website
    );

    if (! $lead) {
        return response()->json([
            'success' => false,
            'message' => 'Active conversation not found.',
        ], 404);
    }

    $this->widgetService->recordVisitorActivity(
    $request,
    $website
);

    return response()->json([
        'success' => true,
        'message' => 'Lead saved successfully.',
        'lead_id' => $lead->id,
    ]);
}
}
