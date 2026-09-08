<?php

namespace App\Http\Controllers\Agent;

use App\Events\LiveChatMessagesRead;
use App\Http\Controllers\Controller;
use App\Models\CannedReply;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\LiveChatSession;
use App\Services\ChatService;
use App\Services\LiveChatAvailabilityService;
use App\Services\LiveChatService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class LiveChatController extends Controller
{
    public function __construct(
        protected LiveChatService $liveChatService,
        protected LiveChatAvailabilityService $availabilityService,
        protected ChatService $chatService
    ) {
    }

    public function dashboard(Request $request): View
    {
        $agent = $request->user();
        $maxActiveChats = $this->maxActiveChatsForAgent($agent);

        return view('agent.dashboard', [
            'availabilityStats' => $this->availabilityService
                ->getCompanyAgentStats($agent->company_id),
            'maxActiveChats' => $maxActiveChats,
            'availabilityStatus' => $agent->availability_status ?? 'offline',
            'waitingCount' => $this->companyConversations($agent)
                ->where('status', 'waiting_agent')
                ->count(),
            'activeCount' => $this->companyConversations($agent)
                ->where('status', 'live_active')
                ->where('assigned_agent_id', $agent->id)
                ->count(),
            'closedTodayCount' => $this->agentClosedSessions($agent)
                ->whereDate('ended_at', today())
                ->count(),
            'waitingConversations' => $this->companyConversations($agent)
                ->with(['visitor', 'website'])
                ->where('status', 'waiting_agent')
                ->orderByRaw('COALESCE(handoff_requested_at, created_at) ASC')
                ->limit(8)
                ->get(),
            'activeConversations' => $this->companyConversations($agent)
                ->with(['visitor', 'website'])
                ->where('status', 'live_active')
                ->where('assigned_agent_id', $agent->id)
                ->latest('updated_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function waiting(Request $request): View
    {
        return view('agent.waiting', [
            'activeCount' => $this->availabilityService
                ->countActiveChatsForAgent($request->user()),
            'maxActiveChats' => $this->maxActiveChatsForAgent($request->user()),
            'conversations' => $this->companyConversations($request->user())
                ->with(['visitor', 'website'])
                ->where('status', 'waiting_agent')
                ->orderByRaw('COALESCE(handoff_requested_at, created_at) ASC')
                ->paginate(20),
        ]);
    }

    public function chats(Request $request): View
    {
        $agent = $request->user();

        return view('agent.chats', [
            'conversations' => $this->companyConversations($agent)
                ->with(['visitor', 'website'])
                ->where('status', 'live_active')
                ->where('assigned_agent_id', $agent->id)
                ->latest('updated_at')
                ->paginate(20),
        ]);
    }

    public function closed(Request $request): View
    {
        return view('agent.closed', [
            'sessions' => $this->agentClosedSessions($request->user())
                ->with([
                    'conversation.visitor',
                    'conversation.website',
                    'agent',
                ])
                ->latest('ended_at')
                ->paginate(20),
        ]);
    }

    public function showClosedSession(
        Request $request,
        LiveChatSession $session
    ): View {
        $session = $this->closedSessionForAgent(
            $request,
            $session
        );

        $conversation = $session->conversation;

        return view('agent.show', [
            'conversation' => $conversation->load([
                'visitor',
                'website',
                'assignedAgent',
                'messages' => fn ($query) => $query
                    ->where('created_at', '<=', $session->ended_at)
                    ->oldest('created_at')
                    ->oldest('id'),
            ]),
            'liveChatSession' => $session,
            'conversationList' => $this->companyConversations($request->user())
                ->with(['visitor', 'website'])
                ->where('status', 'live_active')
                ->where('assigned_agent_id', $request->user()->id)
                ->latest('updated_at')
                ->limit(20)
                ->get(),
            'waitingConversations' => $this->companyConversations($request->user())
                ->with(['visitor', 'website'])
                ->where('status', 'waiting_agent')
                ->orderByRaw('COALESCE(handoff_requested_at, created_at) ASC')
                ->limit(20)
                ->get(),
            'cannedReplies' => collect(),
        ]);
    }

    public function editClosedSessionNote(
        Request $request,
        LiveChatSession $session
    ): View {
        $session = $this->noteSessionForAgent(
            $request,
            $session
        );

        return view('agent.note', [
            'session' => $session,
            'conversation' => $session->conversation,
        ]);
    }

    public function updateClosedSessionNote(
        Request $request,
        LiveChatSession $session
    ): RedirectResponse {
        $session = $this->noteSessionForAgent(
            $request,
            $session
        );

        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
        ]);

        $session->forceFill([
            'note' => trim($data['note']),
            'note_updated_at' => now(),
        ])->save();

        $route = $session->ended_at
            ? route('agent.closed')
            : route('agent.chats.show', $session->conversation);

        return redirect($route)
            ->with('status', 'Internal note saved.');
    }

    public function show(Request $request, ChatConversation $conversation): View
    {
        $conversation = $this->conversationForAgent(
            $request,
            $conversation
        );

        $this->markVisitorMessagesRead($conversation);

        return view('agent.show', [
            'conversation' => $conversation->load([
                'visitor',
                'website',
                'assignedAgent',
                'messages' => fn ($query) =>
                    $query->oldest('created_at')->oldest('id'),
            ]),
            'noteSession' => $this->activeNoteSessionForAgent(
                $request->user(),
                $conversation
            ),
            'conversationList' => $this->companyConversations($request->user())
                ->with(['visitor', 'website'])
                ->where('status', 'live_active')
                ->where('assigned_agent_id', $request->user()->id)
                ->latest('updated_at')
                ->limit(20)
                ->get(),
            'waitingConversations' => $this->companyConversations($request->user())
                ->with(['visitor', 'website'])
                ->where('status', 'waiting_agent')
                ->orderByRaw('COALESCE(handoff_requested_at, created_at) ASC')
                ->limit(20)
                ->get(),
            'cannedReplies' => CannedReply::query()
                ->where('company_id', $request->user()->company_id)
                ->where('website_id', $conversation->website_id)
                ->where('is_active', true)
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function updateAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'availability_status' => [
                'required',
                'string',
                'in:online,away,offline',
            ],
        ]);

        $this->availabilityService->setAvailability(
            $request->user(),
            $data['availability_status']
        );

        return back()->with('status', 'Availability updated.');
    }

    public function accept(
        Request $request,
        ChatConversation $conversation
    ): RedirectResponse {
        try {
            $conversation = $this->liveChatService->acceptConversation(
                $conversation->load('website.settings'),
                $request->user()
            );

            return redirect()
                ->route('agent.chats.show', $conversation)
                ->with('status', 'Conversation accepted.');
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            return back()->withErrors([
                'conversation' => $exception->getMessage(),
            ]);
        }
    }

    public function sendMessage(
        Request $request,
        ChatConversation $conversation
    ): RedirectResponse {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $this->liveChatService->sendAgentMessage(
                $conversation->load('website.settings'),
                $request->user(),
                trim($data['message'])
            );

            return back()->with('status', 'Reply sent.');
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            return back()->withErrors([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function close(
        Request $request,
        ChatConversation $conversation
    ): RedirectResponse {
        try {
            $this->liveChatService->closeConversationAsAgent(
                $conversation->load('website.settings'),
                $request->user()
            );

            return redirect()
                ->route('agent.chats')
                ->with('status', 'Live support ended.');
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            return back()->withErrors([
                'conversation' => $exception->getMessage(),
            ]);
        }
    }

    public function showAttachment(
        Request $request,
        ChatConversation $conversation,
        ChatMessage $message
    ) {
        $agent = $request->user();

        $conversation = $this->companyConversations($agent)
            ->whereKey($conversation->id)
            ->firstOrFail();

        abort_unless($message->conversation_id === $conversation->id, 404);
        abort_unless($this->agentCanViewAttachment($agent, $conversation, $message), 404);

        return $this->chatService->attachmentResponse($message);
    }

    protected function companyConversations($agent): Builder
    {
        return ChatConversation::query()
            ->whereHas(
                'website',
                fn ($query) => $query
                    ->where('company_id', $agent->company_id)
                    ->whereHas(
                        'liveChatAgents',
                        fn ($query) => $query->whereKey($agent->id)
                    )
            );
    }

    protected function agentClosedSessions($agent): Builder
    {
        return LiveChatSession::query()
            ->where('agent_id', $agent->id)
            ->whereNotNull('ended_at')
            ->whereHas(
                'conversation.website',
                fn ($query) => $query->where(
                    'company_id',
                    $agent->company_id
                )
            );
    }

    protected function closedSessionForAgent(
        Request $request,
        LiveChatSession $session
    ): LiveChatSession {
        return $this->agentClosedSessions($request->user())
            ->whereKey($session->id)
            ->with([
                'conversation.visitor',
                'conversation.website',
                'agent',
            ])
            ->firstOrFail();
    }

    protected function noteSessionForAgent(
        Request $request,
        LiveChatSession $session
    ): LiveChatSession {
        $agent = $request->user();

        return LiveChatSession::query()
            ->whereKey($session->id)
            ->where('agent_id', $agent->id)
            ->whereHas(
                'conversation.website',
                fn ($query) => $query->where(
                    'company_id',
                    $agent->company_id
                )
            )
            ->where(function ($query) use ($agent) {
                $query
                    ->whereNotNull('ended_at')
                    ->orWhereHas('conversation', function ($conversationQuery) use ($agent) {
                        $conversationQuery
                            ->where('status', 'live_active')
                            ->where('mode', 'live')
                            ->where('assigned_agent_id', $agent->id);
                    });
            })
            ->with([
                'conversation.visitor',
                'conversation.website',
                'agent',
            ])
            ->firstOrFail();
    }

    protected function activeNoteSessionForAgent(
        $agent,
        ChatConversation $conversation
    ): ?LiveChatSession {
        if (
            $conversation->status !== 'live_active'
            || $conversation->mode !== 'live'
            || $conversation->assigned_agent_id !== $agent->id
        ) {
            return null;
        }

        return LiveChatSession::query()
            ->where('conversation_id', $conversation->id)
            ->where('agent_id', $agent->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->latest('id')
            ->first();
    }

    protected function markVisitorMessagesRead(
        ChatConversation $conversation
    ): void {
        if (
            $conversation->status !== 'live_active'
            || $conversation->mode !== 'live'
        ) {
            return;
        }

        $messageIds = $conversation->messages()
            ->where('sender_type', 'visitor')
            ->whereNull('read_at')
            ->pluck('id')
            ->all();

        if ($messageIds === []) {
            return;
        }

        $conversation->messages()
            ->whereKey($messageIds)
            ->update([
                'read_at' => now(),
            ]);

        broadcast(new LiveChatMessagesRead(
            $conversation,
            array_map('intval', $messageIds)
        ));
    }

    protected function agentCanViewAttachment(
        $agent,
        ChatConversation $conversation,
        ChatMessage $message
    ): bool {
        if (
            $conversation->status === 'live_active'
            && $conversation->assigned_agent_id === $agent->id
        ) {
            return true;
        }

        if (
            in_array($conversation->status, ['closed', 'resolved', 'ended'], true)
            && $conversation->assigned_agent_id === $agent->id
        ) {
            return true;
        }

        return LiveChatSession::query()
            ->where('conversation_id', $conversation->id)
            ->where('agent_id', $agent->id)
            ->whereNotNull('ended_at')
            ->where('ended_at', '>=', $message->created_at)
            ->exists();
    }

    protected function maxActiveChatsForAgent($agent): int
    {
        $website = $agent->company?->websites()
            ->whereHas('settings', fn ($query) =>
                $query->where('enable_live_chat', true)
            )
            ->with('settings')
            ->first();

        if (! $website) {
            return 3;
        }

        return $this->availabilityService
            ->maxActiveChatsForWebsite($website);
    }

    protected function conversationForAgent(
        Request $request,
        ChatConversation $conversation
    ): ChatConversation {
        $agent = $request->user();

        return $this->companyConversations($agent)
            ->whereKey($conversation->id)
            ->where(function ($query) use ($agent) {
                $query
                    ->where(function ($activeQuery) use ($agent) {
                        $activeQuery
                            ->where('status', 'live_active')
                            ->where('assigned_agent_id', $agent->id);
                    })
                    ->orWhere(function ($closedQuery) use ($agent) {
                        $closedQuery
                            ->whereIn('status', ['closed', 'resolved', 'ended'])
                            ->where('assigned_agent_id', $agent->id);
                    });
            })
            ->firstOrFail();
    }
}
