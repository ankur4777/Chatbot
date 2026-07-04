<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Services\AIService;
use App\Services\WidgetService;

class ChatService
{
    protected AIService $aiService;
protected WidgetService $widgetService;

public function __construct(
    AIService $aiService,
    WidgetService $widgetService
)
{
    $this->aiService = $aiService;
    $this->widgetService = $widgetService;
}

public function sendMessage(Request $request)
{
    $website = $this->widgetService->verifyWebsite($request);

if (! $website) {
    return response()->json([
        'success' => false,
        'message' => 'Website not found.',
    ], 404);
}
    $chat = $this->initializeConversation(
    $request,
    $website
);

    $this->saveUserMessage(
        $chat['conversation'],
        $request->message
    );
$aiResponse = $this->generateAIResponse($request->message);

$this->saveBotMessage(
    $chat['conversation'],
    $aiResponse
);

return response()->json([
    'success' => true,
    'response' => $aiResponse,
]);
}
   public function initializeConversation(
    Request $request,
    Website $website
)
{
    $visitor = Visitor::firstOrCreate(
        [
            'website_id' => $website->id,
            'session_id' => $request->session_id,
        ],
        [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]
    );

    $conversation = ChatConversation::firstOrCreate(
        [
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'status' => 'active',
        ],
        [
            'started_at' => now(),
        ]
    );

    

    return [
        'visitor' => $visitor,
        'conversation' => $conversation,
    ];
}

public function saveUserMessage(ChatConversation $conversation, string $message)
{
    return ChatMessage::create([
        'conversation_id' => $conversation->id,
        'sender_type' => 'visitor',
        'message' => $message,
    ]);
}

public function generateAIResponse(string $message): string
{
    return $this->aiService->generateResponse($message);
}

public function saveBotMessage(
    ChatConversation $conversation,
    string $message
)
{
    return ChatMessage::create([
        'conversation_id' => $conversation->id,
        'sender_type' => 'bot',
        'message' => $message,
    ]);
}



public function getQuickReplies()
{

}

}