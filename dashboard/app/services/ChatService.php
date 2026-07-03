<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Services\AIService;

class ChatService
{
    protected AIService $aiService;

public function __construct(AIService $aiService)
{
    $this->aiService = $aiService;
}

public function sendMessage(Request $request)
{
    $chat = $this->initializeConversation($request);

    $this->saveUserMessage(
        $chat['conversation'],
        $request->message
    );
    $aiResponse = $this->generateAIResponse(
    $request->message
);

    return response()->json([
    'success' => true,
    'response' => $aiResponse,
]);
}
    public function initializeConversation(Request $request)
{
    $visitor = Visitor::firstOrCreate(
        [
            'website_id' => $request->website_id,
            'session_id' => $request->session_id,
        ],
        [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]
    );

    $conversation = ChatConversation::firstOrCreate(
        [
            'website_id' => $request->website_id,
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

public function saveBotMessage()
{

}

public function getQuickReplies()
{

}

}