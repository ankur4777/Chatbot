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
    'success' => true,
    'conversation_id' => $conversation->id,
    'response' => $response,
]);
    }

    $data = $this->initializeConversation($request, $website);
    $conversation = $data['conversation'];

    $this->saveUserMessage(
        $conversation,
        $request->message
    );
$history = ChatMessage::where(
    'conversation_id',
    $conversation->id
)
->orderBy('id')
->get();

$messages = [];

foreach ($history as $chat) {

    $messages[] = [
        'role' => $chat->sender_type === 'visitor'
            ? 'user'
            : 'assistant',

        'content' => $chat->message,
    ];
}
\Log::info('History being sent to Python', $messages);
    $response = $this->generateAIResponse(
    $request->message,
    $messages
);

    $this->saveBotMessage(
        $conversation,
        $response
    );

    return response()->json([
        'success' => true,
        'conversation_id' => $conversation->id,
        'response' => $response,
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

    if ($request->conversation_id) {

    $conversation = ChatConversation::where('id', $request->conversation_id)
        ->where('visitor_id', $visitor->id)
        ->first();

if (!$conversation) {
            $conversation = ChatConversation::create([
                'website_id' => $website->id,
                'visitor_id' => $visitor->id,
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

    } else {

        $conversation = ChatConversation::create([
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    

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

public function generateAIResponse(
    string $message,
    array $history
): string
{
    return $this->aiService->generateResponse(
        $message,
        $history
    );
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