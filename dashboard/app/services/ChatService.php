<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Services\AIService;
use App\Services\WidgetService;
use App\Models\ChatbotFlowAnswer;
use App\Models\ChatbotFlowStep;

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

    $data = $this->initializeConversation($request, $website);

    $conversation = $data['conversation'];
    $visitor = $data['visitor'];


$history = ChatMessage::where(
    'conversation_id',
    $conversation->id
)
->latest('id')
->take(10)
->get()
->reverse()
->values();

$messages = [];

foreach ($history as $chat) {

    $messages[] = [
        'role' => $chat->sender_type === 'visitor'
            ? 'user'
            : 'assistant',

        'content' => $chat->message,
    ];
}

$this->saveUserMessage(
        $conversation,
        $request->message
    );

    $aiResponse = $this->generateAIResponse(
        $website->id,
        $request->message,
        $messages,
        $conversation->summary
    );
    $response = $aiResponse['response'] ?? '';

    $this->saveBotMessage(
        $conversation,
        $response

    );if (!empty($aiResponse['summary'])) {
    $conversation->update([
        'summary' => $aiResponse['summary'],
    ]);
}

    if (
    $request->filled('name') ||
    $request->filled('email') ||
    $request->filled('phone')
) {
    $this->saveLead(
        $website,
        $visitor,
        $conversation,
        $request->only([
            'name',
            'email',
            'phone',
            'notes',
        ])
    );
}

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
    ->where('website_id', $website->id)
    ->where('status', 'active')
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
    int $websiteId,
    string $message,
    array $history,
    ?string $summary = null
): array
{
    return $this->aiService->generateResponse(
        $websiteId,
    $message,
    $history,
     $summary,
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

public function saveLead(
    Website $website,
    Visitor $visitor,
    ChatConversation $conversation,
    array $data
)
{
    return \App\Models\ChatbotLead::updateOrCreate(
        [
            'conversation_id' => $conversation->id,
        ],
        [
            'website_id'      => $website->id,
            'visitor_id'      => $visitor->id,
            'name'            => $data['name'] ?? '',
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ]
    );
}
public function endConversation(ChatConversation $conversation)
{
    if ($conversation->status === 'ended') {
        return $conversation;
    }

    $conversation->update([
        'status' => 'ended',
        'ended_at' => now(),
    ]);

    return $conversation;
}
public function saveFlowAnswer(
    Request $request,
    Website $website
) {
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

    $conversation = null;

    if ($request->conversation_id) {
        $conversation = ChatConversation::where('id', $request->conversation_id)
            ->where('visitor_id', $visitor->id)
            ->where('website_id', $website->id)
            ->where('status', 'active')
            ->first();
    }

    if (! $conversation) {
        $conversation = ChatConversation::create([
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    // Get the flow step
    $step = ChatbotFlowStep::find($request->chatbot_flow_step_id);

    if (! $step) {
        return [
            'conversation' => $conversation,
            'answer' => null,
        ];
    }

    // Check if this step already has an answer
    $flowAnswer = ChatbotFlowAnswer::firstOrNew([
        'conversation_id' => $conversation->id,
        'chatbot_flow_step_id' => $request->chatbot_flow_step_id,
    ]);

    $isNewAnswer = ! $flowAnswer->exists;

    $flowAnswer->answer = $request->answer;
    $flowAnswer->save();

    // Save question + answer only when this step is answered first time
    if ($isNewAnswer) {

        // Bot question
        $this->saveBotMessage(
            $conversation,
            $step->question
        );

        // Visitor answer
        $this->saveUserMessage(
            $conversation,
            $request->answer
        );
    }

    return [
        'conversation' => $conversation,
        'answer' => $flowAnswer,
    ];
}

public function saveLeadData(
    Request $request,
    Website $website
) {
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

    $conversation = null;

    if ($request->conversation_id) {
        $conversation = ChatConversation::where('id', $request->conversation_id)
            ->where('visitor_id', $visitor->id)
            ->where('website_id', $website->id)
            ->where('status', 'active')
            ->first();
    }

    if (! $conversation) {
        return null;
    }

    $lead = \App\Models\ChatbotLead::updateOrCreate(
        [
            'conversation_id' => $conversation->id,
        ],
        [
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'notes' => $request->notes,
        ]
    );

    return $lead;
}


public function getQuickReplies()
{

}

}