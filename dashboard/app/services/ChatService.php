<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Website;
use Illuminate\Http\Request;

class ChatService
{

public function sendMessage(Request $request)
{
    $chat = $this->initializeConversation($request);

    $this->saveUserMessage(
        $chat['conversation'],
        $request->message
    );

    return response()->json([
        'success' => true,
        'message' => 'Message received.',
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

public function generateAIResponse()
{

}

public function saveBotMessage()
{

}

public function getQuickReplies()
{

}

}