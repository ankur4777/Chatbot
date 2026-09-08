<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SendMessageRequest;
use App\Services\ChatService;
use App\Services\LiveChatService;
use App\Services\LiveChatAvailabilityService;
use App\Services\WidgetService;
use App\Services\WebsiteFeatureService;
use App\Models\ChatbotLead;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use InvalidArgumentException;
use Pusher\Pusher;

class WidgetController extends Controller
{
   protected WidgetService $widgetService;
protected ChatService $chatService;
protected LiveChatService $liveChatService;
protected LiveChatAvailabilityService $availabilityService;
protected WebsiteFeatureService $websiteFeatureService;

public function __construct(
    WidgetService $widgetService,
    ChatService $chatService,
    LiveChatService $liveChatService,
    LiveChatAvailabilityService $availabilityService,
    WebsiteFeatureService $websiteFeatureService
) {
    $this->widgetService = $widgetService;
    $this->chatService = $chatService;
    $this->liveChatService = $liveChatService;
    $this->availabilityService = $availabilityService;
    $this->websiteFeatureService = $websiteFeatureService;
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

    if (! $this->websiteFeatureService->isChatbotEnabledForWebsite($website)) {
        return response()->json([
            'success' => false,
            'message' => 'Chatbot is disabled for this website.',
        ], 403);
    }

   $this->widgetService->recordVisitorActivity(
    $request,
    $website
);

    try {
        return $this->chatService->sendMessage($request);
    } catch (InvalidArgumentException $exception) {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 409);
    }
}

public function endChat(Request $request)
{
    $request->validate([
        'widget_key' => ['required', 'string'],
        'domain' => ['nullable', 'string'],
        'visitor_uuid' => ['required_with:conversation_id', 'string'],
        'session_id' => ['required_with:conversation_id', 'string'],
        'conversation_id' => ['nullable', 'integer'],
    ]);

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

    $visitor = $this->widgetService->recordVisitorActivity(
        $request,
        $website
    );

    if (! $visitor) {
        return response()->json([
            'success' => false,
            'message' => 'Visitor session could not be verified.',
        ], 422);
    }

    $conversation = ChatConversation::where('id', $request->conversation_id)
        ->where('website_id', $website->id)
        ->where('visitor_id', $visitor->id)
        ->whereIn('status', [
            'active',
            'waiting_agent',
            'live_active',
        ])
        ->first();


    if ($conversation) {
        if (in_array($conversation->status, ['waiting_agent', 'live_active'], true)) {
            $conversation = $this->liveChatService->closeLiveConversation(
                $conversation
            );
        } else {
            $conversation = $this->chatService->endConversation($conversation);
        }
    }

    return response()->json([
        'success' => true,
        'status' => $conversation?->status,
        'pending_rating' => $conversation
            ? $this->liveChatService->pendingRatingPayload(
                $this->liveChatService->pendingRatingForConversation(
                    $conversation
                )
            )
            : null,
        'message' => 'Conversation ended successfully.',
    ]);
}
public function flow(Request $request)
{
    return $this->widgetService->getFlow($request);
}

public function requestLiveChat(Request $request)
{
    $request->validate([
        'widget_key' => ['required', 'string'],
        'domain' => ['required', 'string'],
        'visitor_uuid' => ['required', 'string'],
        'session_id' => ['required', 'string'],
        'conversation_id' => ['required', 'integer'],
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    if (! $this->websiteFeatureService->isLiveChatEnabledForWebsite($website)) {
        return response()->json([
            'success' => false,
            'message' => 'Live Chat is disabled for this website.',
        ], 403);
    }

    $visitor = $this->widgetService->recordVisitorActivity(
        $request,
        $website
    );

    if (! $visitor) {
        return response()->json([
            'success' => false,
            'message' => 'Visitor session could not be verified.',
        ], 422);
    }

    $conversation = ChatConversation::query()
        ->with('website.settings')
        ->whereKey($request->conversation_id)
        ->where('website_id', $website->id)
        ->where('visitor_id', $visitor->id)
        ->first();

    if (! $conversation) {
        return response()->json([
            'success' => false,
            'message' => 'Conversation not found.',
        ], 404);
    }

    if (
        $conversation->status === 'active'
        && ! $this->availabilityService->hasAvailableAgentsForWebsite($website)
    ) {
        $offlineBehavior = $website->settings?->offline_behavior
            ?? 'show_offline_form';

        if ($offlineBehavior === 'hide_button') {
            return response()->json([
                'success' => false,
                'message' => 'No support agents are online right now.',
                'offline_required' => false,
            ], 409);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'status' => $conversation->status,
            'mode' => $conversation->mode,
            'offline_required' => true,
            'message' => $website->settings?->offline_message
                ?: 'Our support team is currently offline. Leave your details and message and we will get back to you.',
        ]);
    }

    try {
        $conversation = $this->liveChatService->requestLiveChat(
            $conversation
        );
    } catch (InvalidArgumentException $exception) {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 409);
    }

    return response()->json([
        'success' => true,
        'conversation_id' => $conversation->id,
        'status' => $conversation->status,
        'mode' => $conversation->mode,
        'handoff_requested_at' => $conversation->handoff_requested_at,
        'message' => match ($conversation->status) {
            'waiting_agent' => $website->settings?->waiting_message
                ?: 'Connecting you to our support team Please wait...',
            'live_active' => 'An agent is already connected.',
            default => 'Live Chat requested.',
        },
    ]);
}

public function saveOfflineLiveChatRequest(Request $request)
{
    $request->validate([
        'widget_key' => ['required', 'string'],
        'domain' => ['required', 'string'],
        'visitor_uuid' => ['required', 'string'],
        'session_id' => ['required', 'string'],
        'conversation_id' => ['required', 'integer'],
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9]{7,15}$/'],
        'message' => ['nullable', 'string', 'max:5000'],
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    if (! $this->websiteFeatureService->isLiveChatEnabledForWebsite($website)) {
        return response()->json([
            'success' => false,
            'message' => 'Live Chat is disabled for this website.',
        ], 403);
    }

    $visitor = $this->widgetService->recordVisitorActivity(
        $request,
        $website
    );

    if (! $visitor) {
        return response()->json([
            'success' => false,
            'message' => 'Visitor session could not be verified.',
        ], 422);
    }

    $conversation = ChatConversation::query()
        ->whereKey($request->integer('conversation_id'))
        ->where('website_id', $website->id)
        ->where('visitor_id', $visitor->id)
        ->whereIn('status', ['active', 'waiting_agent'])
        ->first();

    if (! $conversation) {
        return response()->json([
            'success' => false,
            'message' => 'Conversation not found.',
        ], 404);
    }

    $lead = ChatbotLead::query()->updateOrCreate(
        [
            'conversation_id' => $conversation->id,
        ],
        [
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'source' => 'live_chat_offline_request',
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'notes' => $request->message,
        ]
    );

    return response()->json([
        'success' => true,
        'conversation_id' => $conversation->id,
        'message' => 'Thanks! Your details have been saved.',
        'lead' => [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'message' => $lead->notes,
        ],
    ]);
}

public function realtimeAuth(Request $request)
{
    $request->validate([
        'widget_key' => ['required', 'string'],
        'domain' => ['nullable', 'string'],
        'visitor_uuid' => ['required', 'string'],
        'session_id' => ['required', 'string'],
        'conversation_id' => ['required', 'integer'],
        'socket_id' => ['required', 'string'],
        'channel_name' => ['required', 'string'],
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    if (! $this->websiteFeatureService->isLiveChatEnabledForWebsite($website)) {
        return response()->json([
            'success' => false,
            'message' => 'Live Chat is disabled for this website.',
        ], 403);
    }

    $expectedChannel =
        'private-live-chat.' . $request->integer('conversation_id');

    if ($request->channel_name !== $expectedChannel) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid realtime channel.',
        ], 403);
    }

    $conversation = ChatConversation::query()
        ->whereKey($request->integer('conversation_id'))
        ->where('website_id', $website->id)
        ->whereHas(
            'visitor',
            fn ($query) => $query
                ->where('visitor_uuid', $request->visitor_uuid)
                ->whereHas(
                    'sessions',
                    fn ($sessionQuery) => $sessionQuery->where(
                        'session_id',
                        $request->session_id
                    )
                )
        )
        ->whereIn('status', ['waiting_agent', 'live_active'])
        ->first();

    if (! $conversation) {
        return response()->json([
            'success' => false,
            'message' => 'Conversation not found.',
        ], 403);
    }

    $config = config('broadcasting.connections.reverb');

    try {
        $pusher = new Pusher(
            $config['key'],
            $config['secret'],
            $config['app_id'],
            $config['options'] ?? []
        );

        return response(
            $pusher->authorizeChannel(
                $request->channel_name,
                $request->socket_id
            )
        )->header('Content-Type', 'application/json');
    } catch (\Throwable) {
        return response()->json([
            'success' => false,
            'message' => 'Realtime is currently unavailable.',
        ], 503);
    }
}

public function conversationState(Request $request)
{
    $request->validate([
        'widget_key' => ['required', 'string'],
        'domain' => ['nullable', 'string'],
        'visitor_uuid' => ['required', 'string'],
        'session_id' => ['required', 'string'],
        'conversation_id' => ['required', 'integer'],
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    $conversation = ChatConversation::query()
        ->with([
            'messages' => fn ($query) => $query
                ->orderBy('created_at')
                ->orderBy('id'),
            'flowAnswers.step.options',
        ])
        ->whereKey($request->integer('conversation_id'))
        ->where('website_id', $website->id)
        ->whereHas(
            'visitor',
            fn ($query) => $query
                ->where('visitor_uuid', $request->visitor_uuid)
                ->whereHas(
                    'sessions',
                    fn ($sessionQuery) => $sessionQuery->where(
                        'session_id',
                        $request->session_id
                    )
                )
        )
        ->first();

    if (! $conversation) {
        return response()->json([
            'success' => false,
            'message' => 'Conversation not found.',
        ], 404);
    }

    $messages = $this->conversationTranscriptPayload(
        $conversation,
        $request
    );

    return response()->json([
        'success' => true,
        'conversation_id' => $conversation->id,
        'status' => $conversation->status,
        'mode' => $conversation->mode,
        'messages' => $messages,
        'pending_rating' => $this->liveChatService->pendingRatingPayload(
            $this->liveChatService->pendingRatingForConversation(
                $conversation
            )
        ),
        'realtime_available' => in_array(
            $conversation->status,
            ['waiting_agent', 'live_active'],
            true
        ),
    ]);
}

public function pendingLiveChatRating(Request $request)
{
    $conversation = $this->ratingConversationFromRequest($request);

    if (! $conversation) {
        return response()->json([
            'success' => false,
            'message' => 'Conversation not found.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'pending_rating' => $this->liveChatService->pendingRatingPayload(
            $this->liveChatService->pendingRatingForConversation(
                $conversation
            )
        ),
    ]);
}

public function submitLiveChatRating(Request $request)
{
    $request->validate([
        'live_chat_session_id' => ['required', 'integer'],
        'rating' => ['required', 'integer', 'min:1', 'max:5'],
        'feedback' => ['nullable', 'string', 'max:2000'],
    ]);

    $conversation = $this->ratingConversationFromRequest($request);

    if (! $conversation) {
        return response()->json([
            'success' => false,
            'message' => 'Conversation not found.',
        ], 404);
    }

    try {
        $session = $this->liveChatService->submitRating(
            $conversation,
            $request->integer('live_chat_session_id'),
            $request->integer('rating'),
            $request->input('feedback')
        );
    } catch (InvalidArgumentException $exception) {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 409);
    }

    return response()->json([
        'success' => true,
        'message' => 'Thank you for your feedback!',
        'rating_status' => $session->rating_status,
    ]);
}

public function skipLiveChatRating(Request $request)
{
    $request->validate([
        'live_chat_session_id' => ['required', 'integer'],
    ]);

    $conversation = $this->ratingConversationFromRequest($request);

    if (! $conversation) {
        return response()->json([
            'success' => false,
            'message' => 'Conversation not found.',
        ], 404);
    }

    try {
        $session = $this->liveChatService->skipRating(
            $conversation,
            $request->integer('live_chat_session_id')
        );
    } catch (InvalidArgumentException $exception) {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 409);
    }

    return response()->json([
        'success' => true,
        'rating_status' => $session->rating_status,
    ]);
}

protected function conversationTranscriptPayload(
    ChatConversation $conversation,
    Request $request
) {
    $realMessages = $conversation->messages;
    $flowMessageMatches = $this->matchedFlowMessages($conversation);

    $transcript = $realMessages
        ->map(function ($message) use ($request) {
            $flowMetadata = $message->metadata['flow'] ?? [];

            return [
        'type' => 'chat_message',
        'history_key' => 'chat-message-' . $message->id,
        'id' => $message->id,
        'flow_answer_id' => $flowMetadata['flow_answer_id'] ?? null,
        'flow_step_id' => $flowMetadata['flow_step_id'] ?? null,
        'flow_part' => $flowMetadata['part'] ?? null,
        'sender_type' => $message->sender_type,
        'sender_id' => $message->sender_id,
        'message' => $message->message,
        'attachment' => $this->chatService->attachmentPayload(
            $message,
            $this->widgetAttachmentUrl($request, $message)
        ),
        'created_at' => optional($message->created_at)->toISOString(),
        'sort_time' => optional($message->created_at)->getTimestamp() ?? 0,
        'sort_order' => $message->id * 10,
    ];
        });

    foreach ($conversation->flowAnswers->sortBy([
        ['created_at', 'asc'],
        ['id', 'asc'],
    ]) as $answer) {
        $step = $answer->step;

        if (! $step) {
            continue;
        }

        $createdAt = optional($answer->created_at)->toISOString();
        $sortTime = optional($answer->created_at)->getTimestamp() ?? 0;
        $matchedMessages = $flowMessageMatches->get($answer->id, []);
        $questionSortOrder = isset($matchedMessages['answer'])
            ? ($matchedMessages['answer']->id * 10) - 1
            : ($answer->id * 10) - 1;
        $answerSortOrder = isset($matchedMessages['question'])
            ? ($matchedMessages['question']->id * 10) + 1
            : $answer->id * 10;

        if (! isset($matchedMessages['question'])) {
            $transcript->push([
                'type' => 'flow_question',
                'history_key' => 'flow-question-' . $answer->id . '-' . $step->id,
                'id' => 'flow-question-' . $answer->id . '-' . $step->id,
                'flow_step_id' => $step->id,
                'flow_answer_id' => $answer->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => $step->question,
                'options' => [],
                'attachment' => null,
                'created_at' => $createdAt,
                'sort_time' => $sortTime,
                'sort_order' => $questionSortOrder,
            ]);
        }

        if (! isset($matchedMessages['answer'])) {
            $transcript->push([
                'type' => 'flow_answer',
                'history_key' => 'flow-answer-' . $answer->id,
                'id' => 'flow-answer-' . $answer->id,
                'flow_step_id' => $step->id,
                'flow_answer_id' => $answer->id,
                'sender_type' => 'visitor',
                'sender_id' => null,
                'message' => $answer->answer,
                'attachment' => null,
                'created_at' => $createdAt,
                'sort_time' => $sortTime,
                'sort_order' => $answerSortOrder,
            ]);
        }
    }

    $pendingStep = $this->pendingFlowStepForPreservedLiveChat($conversation);

    if ($pendingStep) {
        $lastAnswer = $conversation->flowAnswers
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->last();
        $lastMatchedMessages = $lastAnswer
            ? $flowMessageMatches->get($lastAnswer->id, [])
            : [];
        $pendingSortOrder = match (true) {
            isset($lastMatchedMessages['answer']) => ($lastMatchedMessages['answer']->id * 10) + 1,
            isset($lastMatchedMessages['question']) => ($lastMatchedMessages['question']->id * 10) + 2,
            default => (($lastAnswer?->id ?? 0) * 10) + 1,
        };

        $transcript->push([
            'type' => 'flow_question',
            'history_key' => 'flow-pending-step-' . $pendingStep->id,
            'id' => 'flow-pending-step-' . $pendingStep->id,
            'flow_step_id' => $pendingStep->id,
            'flow_answer_id' => null,
            'sender_type' => 'bot',
            'sender_id' => null,
            'message' => $pendingStep->question,
            'options' => $pendingStep->options
                ->map(fn ($option) => [
                    'id' => $option->id,
                    'label' => $option->label,
                    'value' => $option->value,
                    'text' => $option->value ?? $option->label,
                    'selected' => false,
                ])
                ->values(),
            'attachment' => null,
            'created_at' => optional($lastAnswer?->created_at)->toISOString(),
            'sort_time' => optional($lastAnswer?->created_at)->getTimestamp() ?? 0,
            'sort_order' => $pendingSortOrder,
        ]);
    }

    return $transcript
        ->sortBy([
            ['sort_time', 'asc'],
            ['sort_order', 'asc'],
        ])
        ->map(fn ($message) => collect($message)
            ->except(['sort_time', 'sort_order'])
            ->all())
        ->values();
}

protected function pendingFlowStepForPreservedLiveChat(
    ChatConversation $conversation
) {
    if (! in_array($conversation->status, ['waiting_agent', 'live_active'], true)) {
        return null;
    }

    $answeredStepIds = $conversation->flowAnswers
        ->pluck('chatbot_flow_step_id');

    if ($answeredStepIds->isEmpty()) {
        return null;
    }

    $lastAnsweredStep = $conversation->flowAnswers
        ->sortBy([
            ['created_at', 'asc'],
            ['id', 'asc'],
        ])
        ->last()
        ?->step;

    if (! $lastAnsweredStep) {
        return null;
    }

    return \App\Models\ChatbotFlowStep::query()
        ->with('options')
        ->where('chatbot_flow_id', $lastAnsweredStep->chatbot_flow_id)
        ->whereNotIn('id', $answeredStepIds)
        ->where('step_order', '>', $lastAnsweredStep->step_order)
        ->orderBy('step_order')
        ->first();
}

protected function matchedFlowMessages(ChatConversation $conversation)
{
    $messages = $conversation->messages
        ->filter(fn ($message) => ! $message->attachment)
        ->values();
    $matches = collect();
    $usedMessageIds = collect();

    foreach ($messages as $message) {
        $flowMetadata = $message->metadata['flow'] ?? null;

        if (
            ! is_array($flowMetadata)
            || empty($flowMetadata['flow_answer_id'])
            || empty($flowMetadata['part'])
        ) {
            continue;
        }

        $answerId = (int) $flowMetadata['flow_answer_id'];
        $part = $flowMetadata['part'];

        if (! in_array($part, ['question', 'answer'], true)) {
            continue;
        }

        $matches->put(
            $answerId,
            array_merge($matches->get($answerId, []), [
                $part => $message,
            ])
        );

        $usedMessageIds->push($message->id);
    }

    $cursor = 0;

    foreach ($conversation->flowAnswers->sortBy([
        ['created_at', 'asc'],
        ['id', 'asc'],
    ]) as $answer) {
        $currentMatch = $matches->get($answer->id, []);
        $step = $answer->step;

        if (! $step) {
            continue;
        }

        $isNearFlowAnswer = function ($message) use ($answer): bool {
            if (! $answer->created_at || ! $message->created_at) {
                return false;
            }

            return abs($message->created_at->diffInSeconds(
                $answer->created_at,
                false
            )) <= 10;
        };

        if (! isset($currentMatch['question'])) {
            for ($index = $cursor; $index < $messages->count(); $index++) {
                $message = $messages[$index];
                $sender = in_array($message->sender_type, ['ai', 'assistant'], true)
                    ? 'bot'
                    : $message->sender_type;

                if (
                    $sender === 'bot'
                    && ! $usedMessageIds->contains($message->id)
                    && $isNearFlowAnswer($message)
                    && trim((string) $message->message) === trim((string) $step->question)
                ) {
                    $currentMatch['question'] = $message;
                    $usedMessageIds->push($message->id);
                    $cursor = $index + 1;
                    break;
                }
            }
        }

        if (! isset($currentMatch['answer'])) {
            for ($index = $cursor; $index < $messages->count(); $index++) {
                $message = $messages[$index];

                if (
                    $message->sender_type === 'visitor'
                    && ! $usedMessageIds->contains($message->id)
                    && $isNearFlowAnswer($message)
                    && trim((string) $message->message) === trim((string) $answer->answer)
                ) {
                    $currentMatch['answer'] = $message;
                    $usedMessageIds->push($message->id);
                    $cursor = $index + 1;
                    break;
                }
            }
        }

        if ($currentMatch) {
            $matches->put($answer->id, $currentMatch);
        }
    }

    return $matches;
}

public function showAttachment(Request $request, ChatMessage $message)
{
    $request->validate([
        'widget_key' => ['required', 'string'],
        'domain' => ['nullable', 'string'],
        'visitor_uuid' => ['required', 'string'],
        'session_id' => ['required', 'string'],
        'conversation_id' => ['required', 'integer'],
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        abort(404);
    }

    $message->loadMissing('conversation');

    $conversation = $message->conversation;

    if (
        ! $conversation
        || (int) $request->integer('conversation_id') !== $conversation->id
        || $conversation->website_id !== $website->id
    ) {
        abort(404);
    }

    $authorized = ChatConversation::query()
        ->whereKey($conversation->id)
        ->where('website_id', $website->id)
        ->whereHas(
            'visitor',
            fn ($query) => $query
                ->where('visitor_uuid', $request->visitor_uuid)
                ->whereHas(
                    'sessions',
                    fn ($sessionQuery) => $sessionQuery->where(
                        'session_id',
                        $request->session_id
                    )
                )
        )
        ->exists();

    abort_unless($authorized, 403);

    return $this->chatService->attachmentResponse($message);
}

protected function widgetAttachmentUrl(
    Request $request,
    ChatMessage $message
): ?string {
    if (! $message->attachment) {
        return null;
    }

    return url('/api/widget/attachments/' . $message->id)
        . '?'
        . http_build_query([
            'widget_key' => $request->widget_key,
            'domain' => $request->domain,
            'visitor_uuid' => $request->visitor_uuid,
            'session_id' => $request->session_id,
            'conversation_id' => $message->conversation_id,
        ]);
}

protected function ratingConversationFromRequest(Request $request): ?ChatConversation
{
    $request->validate([
        'widget_key' => ['required', 'string'],
        'domain' => ['nullable', 'string'],
        'visitor_uuid' => ['required', 'string'],
        'session_id' => ['required', 'string'],
        'conversation_id' => ['required', 'integer'],
    ]);

    $website = $this->widgetService->verifyWebsite($request);

    if (! $website) {
        return null;
    }

    return ChatConversation::query()
        ->whereKey($request->integer('conversation_id'))
        ->where('website_id', $website->id)
        ->whereHas(
            'visitor',
            fn ($query) => $query
                ->where('visitor_uuid', $request->visitor_uuid)
                ->whereHas(
                    'sessions',
                    fn ($sessionQuery) => $sessionQuery->where(
                        'session_id',
                        $request->session_id
                    )
                )
        )
        ->first();
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
        'flow_completed' => $result['flow_completed'] ?? false,
    ]);
}
public function saveLead(Request $request)
{
    $request->validate([
    'widget_key' => 'required',
    'session_id' => 'required',
    'conversation_id' => 'required|integer',

    'name' => [
        'required',
        'string',
        'max:255',
    ],

    'email' => [
        'required',
        'email',
        'max:255',
    ],

    'phone' => [
        'required',
        'string',
        'regex:/^\+?[0-9]{7,15}$/',
    ],

    'notes' => [
        'nullable',
        'string',
        'max:5000',
    ],
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
