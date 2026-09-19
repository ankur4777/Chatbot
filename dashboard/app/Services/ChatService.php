<?php

namespace App\Services;

use App\Events\LiveChatMessageSent;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Services\AIService;
use App\Services\WidgetService;
use App\Models\ChatbotFlowAnswer;
use App\Models\ChatbotFlow;
use App\Models\ChatbotFlowStep;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use App\Services\WebsiteFeatureService;

class ChatService
{
    private const CONTINUABLE_STATUSES = [
        'active',
        'waiting_agent',
        'live_active',
    ];

    protected AIService $aiService;
protected WidgetService $widgetService;
protected WebsiteFeatureService $websiteFeatureService;

public function __construct(
    AIService $aiService,
    WidgetService $widgetService,
    WebsiteFeatureService $websiteFeatureService
)
{
    $this->aiService = $aiService;
    $this->widgetService = $widgetService;
    $this->websiteFeatureService = $websiteFeatureService;
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

    $messageText = trim((string) $request->input('message', ''));
    $attachment = $request->file('attachment');

    if ($messageText === '' && ! $attachment) {
        throw new InvalidArgumentException(
            'Please enter a message or attach a file.'
        );
    }

    if ($attachment && ! $conversation->isLiveActive()) {
        throw new InvalidArgumentException(
            'Attachments are only available during live chat.'
        );
    }

    if ($attachment && ! $this->supportsAttachmentColumns()) {
        throw new InvalidArgumentException(
            'Attachments are not available until the message attachment columns are migrated.'
        );
    }

    $attachmentData = $attachment
        ? $this->storeLiveChatAttachment(
            $conversation,
            $attachment,
            $request->integer('attachment_duration') ?: null
        )
        : null;

    // Save current user message first
    $visitorMessage = $this->saveUserMessage(
        $conversation,
        $messageText,
        $attachmentData
    );

    if (! $conversation->isAiActive()) {
        $this->broadcastSafely(new LiveChatMessageSent($visitorMessage));
        app(AgentNotificationService::class)->visitorMessage($visitorMessage);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'response' => null,
            'ai_response' => null,
            'status' => $conversation->status,
            'mode' => $conversation->mode,
            'message_saved' => true,
            'message_id' => $visitorMessage->id,
            'attachment' => $this->attachmentPayload($visitorMessage),
            'message' => $this->liveChatMessageSavedText(
                $conversation
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Latest 10 messages = AI history
    |--------------------------------------------------------------------------
    */

    if (! $this->websiteFeatureService->isAiResponsesEnabledForWebsite($website)) {
        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'response' => null,
            'ai_response' => null,
            'status' => $conversation->status,
            'mode' => $conversation->mode,
            'message_saved' => true,
            'message_id' => $visitorMessage->id,
            'ai_responses_enabled' => false,
            'message' => $this->aiDisabledMessage($conversation, $website),
        ]);
    }

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

    /*
    |--------------------------------------------------------------------------
    | Messages older than latest 10 = Summary source
    |--------------------------------------------------------------------------
    */

    $olderMessages = ChatMessage::where(
        'conversation_id',
        $conversation->id
    )
        ->orderBy('id', 'asc')
        ->get();

    // Remove latest 10 messages from summary source
    if ($olderMessages->count() > 10) {
        $olderMessages = $olderMessages
            ->slice(0, $olderMessages->count() - 10)
            ->values();
    } else {
        $olderMessages = collect();
    }

    $summaryMessages = [];

    foreach ($olderMessages as $chat) {

        // Summary should contain only user information
        if ($chat->sender_type !== 'visitor') {
            continue;
        }

        $summaryMessages[] = [
            'role' => 'user',
            'content' => $chat->message,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | AI Response
    |--------------------------------------------------------------------------
    */

    $aiResponse = $this->generateAIResponse(
    $website->id,
    $messageText,
    $messages,
    $conversation->summary,
    $summaryMessages
);

    $response = $aiResponse['response'] ?? '';

    $this->saveBotMessage(
        $conversation,
        $response
    );

    /*
    |--------------------------------------------------------------------------
    | Update conversation summary
    |--------------------------------------------------------------------------
    */

    if (! empty($aiResponse['summary'])) {

        $conversation->update([
            'summary' => $aiResponse['summary'],
        ]);

        $conversation->refresh();
    }

    /*
    |--------------------------------------------------------------------------
    | Lead
    |--------------------------------------------------------------------------
    */

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
        'status' => $conversation->status,
        'mode' => $conversation->mode,
        'message_saved' => true,
        'message_id' => $visitorMessage->id,
    ]);
}
   public function initializeConversation(
    Request $request,
    Website $website
)
{
    $visitor = $this->getOrCreateVisitor(
    $request,
    $website
);

    $conversation = null;

    if ($request->conversation_id) {

    $conversation = ChatConversation::where('id', $request->conversation_id)
    ->where('visitor_id', $visitor->id)
    ->where('website_id', $website->id)
    ->whereIn('status', self::CONTINUABLE_STATUSES)
    ->first();

if (!$conversation) {
            $endedConversation = ChatConversation::where('id', $request->conversation_id)
                ->where('visitor_id', $visitor->id)
                ->where('website_id', $website->id)
                ->whereIn('status', ['closed', 'resolved', 'ended'])
                ->first();

            if ($endedConversation) {
                throw new \InvalidArgumentException(
                    'This conversation has ended.'
                );
            }

            $this->endActiveVisitorConversations($website, $visitor);

            $conversation = ChatConversation::create([
                'website_id' => $website->id,
                'visitor_id' => $visitor->id,
                'status' => 'active',
                'mode' => 'ai',
                'started_at' => now(),
            ]);
        }

    } else {
        $this->endActiveVisitorConversations($website, $visitor);

        $conversation = ChatConversation::create([
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'status' => 'active',
            'mode' => 'ai',
            'started_at' => now(),
        ]);
    }

    

    return [
        'visitor' => $visitor,
        'conversation' => $conversation,
    ];
}

public function saveUserMessage(
    ChatConversation $conversation,
    string $message,
    ?array $attachmentData = null,
    ?array $metadata = null
)
{
    $payload = [
        'conversation_id' => $conversation->id,
        'sender_type' => 'visitor',
        'message' => $message,
    ];

    if ($attachmentData && $this->supportsAttachmentColumns()) {
        $payload['attachment'] = $attachmentData['path'];
        $payload['attachment_type'] = $attachmentData['type'];
    }

    if ($attachmentData && Schema::hasColumn('chat_messages', 'metadata')) {
        $payload['metadata'] = [
            'attachment' => $attachmentData['metadata'],
        ];
    }

    if ($metadata && Schema::hasColumn('chat_messages', 'metadata')) {
        $payload['metadata'] = array_merge(
            $payload['metadata'] ?? [],
            $metadata
        );
    }

    return ChatMessage::create($payload);
}

public function storeLiveChatAttachment(
    ChatConversation $conversation,
    UploadedFile $file,
    ?int $durationSeconds = null
): array {
    $extension = strtolower($file->getClientOriginalExtension());
    $mimeType = $file->getMimeType();

    $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'pdf',
        'mp4',
        'webm',
        'mov',
        'ogg',
        'm4a',
        'mp3',
    ];

    $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
        'video/mp4',
        'video/webm',
        'video/quicktime',
        'video/ogg',
        'audio/webm',
        'audio/ogg',
        'audio/mpeg',
        'audio/mp4',
        'audio/x-m4a',
        'audio/m4a',
    ];

    if (
        ! in_array($extension, $allowedExtensions, true)
        || ! in_array($mimeType, $allowedMimeTypes, true)
    ) {
            throw new InvalidArgumentException(
                'Upload an image, PDF, video, or voice note up to 10 MB.'
            );
    }

    $isVoiceNote =
    $durationSeconds !== null
    && in_array($extension, ['webm', 'ogg', 'm4a', 'mp3'], true)
    && (
        str_starts_with($mimeType, 'audio/')
        || in_array(
            $mimeType,
            [
                'video/webm',
                'video/ogg',
                'video/mp4',
            ],
            true
        )
    );

$type = $isVoiceNote
    ? 'audio'
    : match (true) {
        str_starts_with($mimeType, 'audio/') => 'audio',
        str_starts_with($mimeType, 'video/') => 'video',
        $mimeType === 'application/pdf' => 'pdf',
        default => 'image',
    };

$normalizedMimeType = $isVoiceNote
    ? match ($extension) {
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/mp4',
        'mp3' => 'audio/mpeg',
        default => 'audio/webm',
    }
    : $mimeType;

    $path = $file->storeAs(
        'live-chat/' . $conversation->id,
        (string) Str::uuid() . '.' . $extension,
        'local'
    );

    if (! $path) {
        throw new InvalidArgumentException(
            'Unable to upload the file. Please try again.'
        );
    }

    return [
        'path' => $path,
        'type' => $type,
        'metadata' => [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $normalizedMimeType,
            'size' => $file->getSize(),
            'extension' => $extension,
            'duration' => $durationSeconds,
        ],
    ];
}

public function attachmentPayload(
    ChatMessage $message,
    ?string $viewUrl = null
): ?array {
    if (! $message->attachment) {
        return null;
    }

    $metadata = $message->metadata['attachment'] ?? [];

    return [
        'name' => $metadata['original_name'] ?? basename($message->attachment),
        'type' => $message->attachment_type,
        'mime_type' => $metadata['mime_type'] ?? null,
        'size' => $metadata['size'] ?? null,
        'duration' => $metadata['duration'] ?? null,
        'view_url' => $viewUrl,
    ];
}

protected function supportsAttachmentColumns(): bool
{
    return Schema::hasColumn('chat_messages', 'attachment')
        && Schema::hasColumn('chat_messages', 'attachment_type');
}

protected function aiDisabledMessage(
    ChatConversation $conversation,
    Website $website
): string {
    $pendingStep = $this->currentPendingFlowStep($conversation, $website);

    if ($pendingStep && $pendingStep->options->isNotEmpty()) {
        return 'Please select an option above.';
    }

    return 'Your message has been saved.';
}

protected function currentPendingFlowStep(
    ChatConversation $conversation,
    Website $website
): ?ChatbotFlowStep {
    $flow = ChatbotFlow::query()
        ->where('website_id', $website->id)
        ->where('is_active', true)
        ->first();

    if (! $flow) {
        return null;
    }

    $answeredStepIds = ChatbotFlowAnswer::query()
        ->where('conversation_id', $conversation->id)
        ->pluck('chatbot_flow_step_id');

    return ChatbotFlowStep::query()
        ->with([
            'options' => fn ($query) => $query->orderBy('sort_order'),
        ])
        ->where('chatbot_flow_id', $flow->id)
        ->when(
            $answeredStepIds->isNotEmpty(),
            fn ($query) => $query->whereNotIn('id', $answeredStepIds)
        )
        ->orderBy('step_order')
        ->first();
}

public function attachmentResponse(ChatMessage $message)
{
    if (! $message->attachment || ! Storage::disk('local')->exists($message->attachment)) {
        abort(404);
    }

    $metadata = $message->metadata['attachment'] ?? [];

    return Storage::disk('local')->response(
        $message->attachment,
        $metadata['original_name'] ?? basename($message->attachment),
        [
            'Content-Type' => $metadata['mime_type'] ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]
    );
}

public function generateAIResponse(
    int $websiteId,
    string $message,
    array $history,
    ?string $summary = null,
    array $summaryMessages = []
): array
{
    return $this->aiService->generateResponse(
        $websiteId,
        $message,
        $history,
        $summary,
        $summaryMessages
    );
}

public function saveBotMessage(
    ChatConversation $conversation,
    string $message,
    ?array $metadata = null
)
{
    $payload = [
        'conversation_id' => $conversation->id,
        'sender_type' => 'bot',
        'message' => $message,
    ];

    if ($metadata && Schema::hasColumn('chat_messages', 'metadata')) {
        $payload['metadata'] = $metadata;
    }

    return ChatMessage::create($payload);
}

private function liveChatMessageSavedText(
    ChatConversation $conversation
): string {
    if ($conversation->isWaitingForAgent()) {
        return 'Message saved.';
    }

    if ($conversation->isLiveActive()) {
        return 'Message saved.';
    }

    return 'Message saved.';
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

private function endActiveVisitorConversations(
    Website $website,
    Visitor $visitor,
    ?int $exceptConversationId = null
): void {
    ChatConversation::query()
        ->where('website_id', $website->id)
        ->where('visitor_id', $visitor->id)
        ->where('status', 'active')
        ->when(
            $exceptConversationId,
            fn ($query) => $query->whereKeyNot($exceptConversationId),
        )
        ->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);
}

public function saveFlowAnswer(
    Request $request,
    Website $website
) {
    $visitor = $this->getOrCreateVisitor(
    $request,
    $website
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
        $this->endActiveVisitorConversations($website, $visitor);

        $conversation = ChatConversation::create([
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'status' => 'active',
            'mode' => 'ai',
            'started_at' => now(),
        ]);
    }

    // Get the flow step
    $step = ChatbotFlowStep::find($request->chatbot_flow_step_id);

    if (! $step) {
        return [
            'conversation' => $conversation,
            'answer' => null,
            'flow_completed' => false,
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
        $flowMetadata = [
            'flow' => [
                'flow_answer_id' => $flowAnswer->id,
                'flow_step_id' => $step->id,
                'source' => 'chatbot_flow',
            ],
        ];

        // Bot question
        $this->saveBotMessage(
            $conversation,
            $step->question,
            array_merge($flowMetadata, [
                'flow' => array_merge($flowMetadata['flow'], [
                    'part' => 'question',
                ]),
            ])
        );

        // Visitor answer
        $this->saveUserMessage(
            $conversation,
            $request->answer,
            null,
            array_merge($flowMetadata, [
                'flow' => array_merge($flowMetadata['flow'], [
                    'part' => 'answer',
                ]),
            ])
        );
    }

    $hasNextStep = ChatbotFlowStep::query()
        ->where('chatbot_flow_id', $step->chatbot_flow_id)
        ->where('step_order', '>', $step->step_order)
        ->exists();

    return [
        'conversation' => $conversation,
        'answer' => $flowAnswer,
        'flow_completed' => ! $hasNextStep,
    ];
}

public function saveLeadData(
    Request $request,
    Website $website
) {
    $visitor = $this->getOrCreateVisitor(
    $request,
    $website
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

protected function getOrCreateVisitor(
    Request $request,
    Website $website
): Visitor {
    $visitorUuid = $request->input('visitor_uuid');
    $sessionId = $request->input('session_id');

    if (! $visitorUuid) {
        $visitorUuid = (string) Str::uuid();
    }

    if (! $sessionId) {
        $sessionId = (string) Str::uuid();
    }

    $visitor = Visitor::firstOrCreate(
        [
            'website_id' => $website->id,
            'visitor_uuid' => $visitorUuid,
        ],
        [
            'first_seen_at' => now(),
            'last_activity_at' => now(),
            'ip_address' => $request->ip(),
                    ]
    );

    $visitor->update([
        'last_activity_at' => now(),
        'ip_address' => $request->ip(),
    ]);

    VisitorSession::firstOrCreate(
        [
            'session_id' => $sessionId,
        ],
        [
            'visitor_id' => $visitor->id,
            'ip_address' => $request->ip(),
            'started_at' => now(),
            'last_activity_at' => now(),
        ]
    );

VisitorSession::where('session_id', $sessionId)
        ->update([
            'last_activity_at' => now(),
            'ip_address' => $request->ip(),
        ]);

    return $visitor;
}

protected function broadcastSafely(object $event): void
{
    try {
        broadcast($event);
    } catch (\Throwable $exception) {
        Log::warning('Live chat visitor broadcast failed.', [
            'event' => $event::class,
            'message' => $exception->getMessage(),
        ]);
    }
}

}
