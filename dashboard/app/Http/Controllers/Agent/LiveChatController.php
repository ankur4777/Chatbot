<?php

namespace App\Http\Controllers\Agent;

use App\Events\LiveChatMessageSent;
use App\Events\LiveChatMessagesRead;
use App\Http\Controllers\Controller;
use App\Models\CannedReply;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\LiveChatRating;
use App\Models\LiveChatSession;
use App\Models\Website;
use App\Services\ChatService;
use App\Services\LiveChatAvailabilityService;
use App\Services\LiveChatService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

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
            'availabilityStats' => $this->availabilityStatsForAgent($agent),
            'maxActiveChats' => $maxActiveChats,
            'availabilityStatus' => $agent->availability_status ?? 'offline',
            'waitingCount' => $this->recentCompanyConversations($agent)
                ->where('status', 'waiting_agent')
                ->count(),
            'activeCount' => $this->recentCompanyConversations($agent)
                ->where('status', 'live_active')
                ->where('assigned_agent_id', $agent->id)
                ->count(),
            'closedTodayCount' => $this->agentClosedSessions($agent)
                ->whereDate('ended_at', today())
                ->count(),
            'waitingConversations' => $this->recentCompanyConversations($agent)
                ->with(['visitor', 'website'])
                ->where('status', 'waiting_agent')
                ->orderByRaw('COALESCE(handoff_requested_at, created_at) ASC')
                ->limit(8)
                ->get(),
            'activeConversations' => $this->recentCompanyConversations($agent)
                ->with(['visitor', 'website', 'activeLiveChatSession'])
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
            'conversations' => $this->recentCompanyConversations($request->user())
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
                ->with(['visitor', 'website', 'activeLiveChatSession'])
                ->where('status', 'live_active')
                ->where('updated_at', '>=', $this->recentChatCutoff())
                ->where('assigned_agent_id', $agent->id)
                ->latest('updated_at')
                ->paginate(20),
        ]);
    }

    public function closed(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $visitorSearch = trim((string) preg_replace('/^visitor\s+/i', '', $search));

        $sessions = $this->agentClosedSessions($request->user())
            ->select('live_chat_sessions.*')
            ->where('ended_at', '>=', $this->recentChatCutoff())
            ->when($search !== '', function ($query) use ($search, $visitorSearch) {
                $query->where(function ($searchQuery) use ($search, $visitorSearch) {
                    $searchQuery
                        ->where('live_chat_sessions.ended_by', 'like', "%{$search}%")
                        ->orWhere('live_chat_sessions.note', 'like', "%{$search}%")
                        ->orWhereHas('conversation.visitor', function ($visitorQuery) use ($search) {
                            $visitorQuery
                                ->where('visitor_uuid', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->when($visitorSearch !== '', function ($query) use ($visitorSearch) {
                            $query->orWhereHas('conversation.visitor', function ($visitorQuery) use ($visitorSearch) {
                                $visitorQuery
                                    ->where('visitor_uuid', 'like', "%{$visitorSearch}%")
                                    ->orWhere('name', 'like', "%{$visitorSearch}%");
                            });
                        })
                        ->orWhereHas('conversation.website', function ($websiteQuery) use ($search) {
                            $websiteQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('agent', function ($agentQuery) use ($search) {
                            $agentQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('conversation.messages', function ($messageQuery) use ($search) {
                            $messageQuery->where('message', 'like', "%{$search}%");
                        });
                });
            })
            ->addSelect([
                'conversation_rating_average' => LiveChatRating::query()
                    ->from('live_chat_ratings as rated_sessions')
                    ->selectRaw('AVG(rated_sessions.rating)')
                    ->whereColumn(
                        'rated_sessions.conversation_id',
                        'live_chat_sessions.conversation_id'
                    ),
                'conversation_rating_count' => LiveChatRating::query()
                    ->from('live_chat_ratings as rated_sessions')
                    ->selectRaw('COUNT(rated_sessions.rating)')
                    ->whereColumn(
                        'rated_sessions.conversation_id',
                        'live_chat_sessions.conversation_id'
                    ),
            ])
            ->with([
                'conversation.visitor',
                'conversation.website',
                'conversation.messages' => fn ($query) => $query
                    ->latest('id'),
                'agent',
            ])
            ->latest('ended_at')
            ->paginate(20)
            ->withQueryString();

        return view('agent.closed', [
            'sessions' => $sessions,
            'search' => $search,
        ]);
    }

    public function cannedReplies(Request $request): View
    {
        $agent = $request->user();
        $websites = $agent->assignedWebsites()
            ->where('websites.company_id', $agent->company_id)
            ->orderBy('name')
            ->get();

        if ($websites->isEmpty()) {
            return view('agent.canned-replies', [
                'websites' => $websites,
                'selectedWebsite' => null,
                'replies' => collect(),
                'editingReply' => null,
            ]);
        }

        $website = $this->agentCannedReplyWebsite($request);
        $editingReply = null;

        if ($request->filled('edit')) {
            $editingReply = $this->agentCannedReply(
                $request,
                (int) $request->query('edit'),
                $website->id
            );
        }

        return view('agent.canned-replies', [
            'websites' => $websites,
            'selectedWebsite' => $website,
            'replies' => CannedReply::query()
                ->where('company_id', $agent->company_id)
                ->where('website_id', $website->id)
                ->where('agent_id', $agent->id)
                ->latest()
                ->get(),
            'editingReply' => $editingReply,
        ]);
    }

    public function storeCannedReply(Request $request): RedirectResponse
    {
        $website = $this->agentCannedReplyWebsite($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CannedReply::query()->create([
            'company_id' => $request->user()->company_id,
            'website_id' => $website->id,
            'agent_id' => $request->user()->id,
            'title' => trim($data['title']),
            'message' => trim($data['message']),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()
            ->route('agent.canned-replies', ['website' => $website->id])
            ->with('status', 'Canned reply saved.');
    }

    public function updateCannedReply(
        Request $request,
        CannedReply $reply
    ): RedirectResponse {
        $website = $this->agentCannedReplyWebsite($request);
        $reply = $this->agentCannedReply($request, $reply->id, $website->id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $reply->update([
            'title' => trim($data['title']),
            'message' => trim($data['message']),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()
            ->route('agent.canned-replies', ['website' => $website->id])
            ->with('status', 'Canned reply saved.');
    }

    public function deleteCannedReply(
        Request $request,
        CannedReply $reply
    ): RedirectResponse {
        $website = $this->agentCannedReplyWebsite($request);
        $reply = $this->agentCannedReply($request, $reply->id, $website->id);

        $reply->delete();

        return redirect()
            ->route('agent.canned-replies', ['website' => $website->id])
            ->with('status', 'Canned reply deleted.');
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
                'visitor.detailsUpdatedBy',
                'website',
                'assignedAgent',
                'messages' => fn ($query) => $query
                    ->where('created_at', '<=', $session->ended_at)
                    ->oldest('created_at')
                    ->oldest('id'),
            ]),
            'liveChatSession' => $session,
            'conversationList' => $this->companyConversations($request->user())
                ->with(['visitor', 'website', 'activeLiveChatSession'])
                ->where('status', 'live_active')
                ->where('updated_at', '>=', $this->recentChatCutoff())
                ->where('assigned_agent_id', $request->user()->id)
                ->latest('updated_at')
                ->limit(20)
                ->get(),
            'waitingConversations' => $this->companyConversations($request->user())
                ->with(['visitor', 'website'])
                ->where('status', 'waiting_agent')
                ->where('updated_at', '>=', $this->recentChatCutoff())
                ->orderByRaw('COALESCE(handoff_requested_at, created_at) ASC')
                ->limit(20)
                ->get(),
            'cannedReplies' => collect(),
        ]);
    }

    public function downloadClosedSession(
        Request $request,
        LiveChatSession $session
    ): Response {
        $session = $this->closedSessionForAgent(
            $request,
            $session
        );

        $session->load([
            'conversation.messages' => fn ($query) => $query
                ->where('created_at', '<=', $session->ended_at)
                ->oldest('created_at')
                ->oldest('id'),
        ]);

        return response($this->closedSessionPdfContent($session), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="closed-chat-' . $session->conversation_id . '.pdf"',
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

        $noteSession = $this->activeNoteSessionForAgent(
            $request->user(),
            $conversation
        );

        return view('agent.show', [
            'conversation' => $conversation->load([
                'visitor',
                'visitor.detailsUpdatedBy',
                'website',
                'assignedAgent',
                'activeLiveChatSession',
                'messages' => fn ($query) =>
                    $query->oldest('created_at')->oldest('id'),
            ]),
            'noteSession' => $noteSession,
            'activeLiveChatSession' => $noteSession,
            'conversationList' => $this->companyConversations($request->user())
                ->with(['visitor', 'website', 'activeLiveChatSession'])
                ->where('status', 'live_active')
                ->where('updated_at', '>=', $this->recentChatCutoff())
                ->where('assigned_agent_id', $request->user()->id)
                ->latest('updated_at')
                ->limit(20)
                ->get(),
            'waitingConversations' => $this->companyConversations($request->user())
                ->with(['visitor', 'website'])
                ->where('status', 'waiting_agent')
                ->where('updated_at', '>=', $this->recentChatCutoff())
                ->orderByRaw('COALESCE(handoff_requested_at, created_at) ASC')
                ->limit(20)
                ->get(),
            'cannedReplies' => CannedReply::query()
                ->where('company_id', $request->user()->company_id)
                ->where('website_id', $conversation->website_id)
                ->where('agent_id', $request->user()->id)
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

    public function updateChatStatus(
        Request $request,
        ChatConversation $conversation
    ): JsonResponse|RedirectResponse {
        $data = $request->validate([
            'agent_chat_status' => [
                'required',
                'string',
                'in:' . implode(',', LiveChatSession::agentChatStatuses()),
            ],
        ]);

        try {
            $session = $this->liveChatService->updateAgentChatStatus(
                $conversation,
                $request->user(),
                $data['agent_chat_status']
            );

            $payload = [
                'agent_chat_status' => $session->agent_chat_status,
                'label' => $session->agentChatStatusLabel(),
            ];

            if ($request->expectsJson()) {
                return response()->json($payload);
            }

            return back()->with('status', 'Chat status updated.');
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return back()->withErrors([
                'agent_chat_status' => $exception->getMessage(),
            ]);
        }
    }

    public function updateVisitorDetails(
        Request $request,
        ChatConversation $conversation
    ): RedirectResponse|JsonResponse {
        $agent = $request->user();

        $conversation = $this->companyConversations($agent)
            ->with('visitor')
            ->whereKey($conversation->id)
            ->firstOrFail();

        $canUpdateVisitor =
            $conversation->assigned_agent_id === $agent->id
            || LiveChatSession::query()
                ->where('conversation_id', $conversation->id)
                ->where('agent_id', $agent->id)
                ->exists();

        abort_unless($canUpdateVisitor, 403);
        abort_unless($conversation->visitor, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $conversation->visitor->forceFill([
            'name' => trim($data['name']),
            'email' => filled($data['email'] ?? null) ? trim($data['email']) : null,
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
            'details_updated_by' => $agent->id,
            'details_updated_at' => now(),
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Visitor details saved.',
                'visitor' => [
                    'name' => $conversation->visitor->displayName(),
                    'email' => $conversation->visitor->email,
                    'phone' => $conversation->visitor->phone,
                    'initials' => $conversation->visitor->initials(),
                ],
            ]);
        }

        return back()->with('status', 'Visitor details saved.');
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
    ): RedirectResponse|JsonResponse {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,pdf,mp4,webm,mov,ogg,m4a,mp3',
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf,video/mp4,video/webm,video/quicktime,video/ogg,audio/webm,audio/ogg,audio/mpeg,audio/mp4,audio/x-m4a,audio/m4a',
            ],
            'attachment_duration' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        try {
            $message = trim((string) ($data['message'] ?? ''));
            $attachment = $request->file('attachment');

            if ($message === '' && ! $attachment) {
                throw new InvalidArgumentException(
                    'Please enter a message or attach a file.'
                );
            }

            $conversation = $conversation->load('website.settings');

            if ($attachment && ($conversation->status !== 'live_active' || $conversation->mode !== 'live')) {
                throw new InvalidArgumentException(
                    'Attachments are only available during live chat.'
                );
            }

            $attachmentData = $attachment
                ? $this->chatService->storeLiveChatAttachment(
                    $conversation,
                    $attachment,
                    $request->integer('attachment_duration') ?: null
                )
                : null;

            $chatMessage = $this->liveChatService->sendAgentMessage(
                $conversation,
                $request->user(),
                $message,
                $attachmentData
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Reply sent.',
                    'chat_message' => (new LiveChatMessageSent($chatMessage))->broadcastWith(),
                ]);
            }

            return back()->with('status', 'Reply sent.');
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 422);
            }

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

    protected function recentCompanyConversations($agent): Builder
    {
        return $this->companyConversations($agent)
            ->where('updated_at', '>=', $this->recentChatCutoff());
    }

    protected function recentChatCutoff()
    {
        return now()->subDays(30);
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

    protected function agentCannedReplyWebsite(Request $request): Website
    {
        $agent = $request->user();
        $websiteId = $request->integer('website')
            ?: $agent->assignedWebsites()
                ->where('websites.company_id', $agent->company_id)
                ->orderBy('name')
                ->value('websites.id');

        abort_unless($websiteId, 404);

        return $agent->assignedWebsites()
            ->where('websites.company_id', $agent->company_id)
            ->whereKey($websiteId)
            ->firstOrFail();
    }

    protected function agentCannedReply(
        Request $request,
        int $replyId,
        int $websiteId
    ): CannedReply {
        return CannedReply::query()
            ->whereKey($replyId)
            ->where('company_id', $request->user()->company_id)
            ->where('website_id', $websiteId)
            ->where('agent_id', $request->user()->id)
            ->firstOrFail();
    }

    protected function closedSessionPdfContent(LiveChatSession $session): string
    {
        $lines = $this->closedSessionPdfLines($session);
        $pages = array_chunk($lines, 52);
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pageObjectNumbers = [];

        foreach ($pages as $pageLines) {
            $contentObjectNumber = count($objects) + 1;
            $pageObjectNumber = $contentObjectNumber + 1;
            $pageObjectNumbers[] = $pageObjectNumber;
            $stream = "BT\n/F1 10 Tf\n40 800 Td\n14 TL\n";

            foreach ($pageLines as $line) {
                $stream .= '(' . $this->pdfEscape($line) . ") Tj\nT*\n";
            }

            $stream .= "ET\n";
            $objects[] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentObjectNumber . ' 0 R >>';
        }

        $objects[1] = '<< /Type /Pages /Kids ['
            . collect($pageObjectNumbers)->map(fn ($number) => $number . ' 0 R')->implode(' ')
            . '] /Count ' . count($pageObjectNumbers) . ' >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }

        return $pdf
            . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n"
            . "startxref\n" . $xrefOffset . "\n%%EOF";
    }

    protected function closedSessionPdfLines(LiveChatSession $session): array
    {
        $conversation = $session->conversation;
        $visitor = $conversation?->visitor?->displayName() ?? 'Unknown Visitor';

        $lines = [
            'Closed Chat Transcript',
            'Generated: ' . \App\Support\BrowserTime::format(now(), 'd M Y, h:i A'),
            '',
            'Conversation #' . ($conversation?->id ?? 'N/A'),
            'Website: ' . ($conversation?->website?->name ?? 'N/A'),
            'Visitor: ' . $visitor,
            'Visitor Email: ' . ($conversation?->visitor?->email ?: 'N/A'),
            'Visitor Phone: ' . ($conversation?->visitor?->phone ?: 'N/A'),
            'Agent: ' . ($session->agent?->name ?? 'Unassigned'),
            'Started: ' . ($session->started_at ? \App\Support\BrowserTime::format($session->started_at, 'd M Y, h:i A') : 'N/A'),
            'Closed: ' . ($session->ended_at ? \App\Support\BrowserTime::format($session->ended_at, 'd M Y, h:i A') : 'N/A'),
            'Closed By: ' . ucfirst((string) ($session->ended_by ?? 'N/A')),
            'Duration: ' . $this->formatClosedSessionDuration($session),
            'Rating: ' . ($session->rating_status === 'submitted' && $session->rating ? $session->rating . '/5' : 'Not Rated'),
            'Feedback: ' . ($session->feedback ?: 'N/A'),
            'Agent Note: ' . ($session->note ?: 'N/A'),
            '',
            'Messages',
            '--------',
        ];

        $messages = $conversation?->messages ?? collect();

        if ($messages->isEmpty()) {
            return array_merge($lines, ['No messages found.']);
        }

        foreach ($messages as $message) {
            $text = trim((string) $message->message);

            if ($text === '' && $message->attachment) {
                $text = 'Attachment';
            }

            if ($message->attachment) {
                $text .= ($text === '' ? '' : ' ')
                    . '[Attachment: ' . ($message->attachment_type ?: 'file') . ']';
            }

            $lines[] = '[' . ($message->created_at ? \App\Support\BrowserTime::format($message->created_at, 'd M Y, h:i A') : 'N/A') . '] '
                . $this->closedSessionSenderLabel($message->sender_type) . ':';

            foreach ($this->wrapPdfLine($text ?: 'N/A') as $wrappedLine) {
                $lines[] = '  ' . $wrappedLine;
            }

            $lines[] = '';
        }

        return $lines;
    }

    protected function wrapPdfLine(string $line): array
    {
        return explode("\n", wordwrap($this->pdfText($line), 92, "\n", true));
    }

    protected function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->pdfText($text));
    }

    protected function pdfText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';

        return trim(preg_replace('/\s+/', ' ', $text) ?? '');
    }

    protected function closedSessionSenderLabel(?string $senderType): string
    {
        return match ($senderType) {
            'visitor', 'user' => 'Visitor',
            'bot', 'assistant', 'ai' => 'AI Assistant',
            'agent' => 'Agent',
            null, '' => '',
            default => ucfirst($senderType),
        };
    }

    protected function formatClosedSessionDuration(LiveChatSession $session): string
    {
        if (! $session->started_at || ! $session->ended_at) {
            return '';
        }

        $seconds = $session->started_at->diffInSeconds($session->ended_at, true);
        $minutes = intdiv($seconds, 60);

        if ($seconds < 60) {
            return $seconds . ' sec';
        }

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0
            ? $hours . ' hr ' . $remainingMinutes . ' min'
            : $hours . ' hr';
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
        $website = $this->liveChatWebsiteForAgent($agent);

        if (! $website) {
            return 3;
        }

        return $this->availabilityService
            ->maxActiveChatsForWebsite($website);
    }

    protected function availabilityStatsForAgent($agent): array
    {
        $website = $this->liveChatWebsiteForAgent($agent);

        if (! $website) {
            return [
                'total' => 0,
                'online' => 0,
                'available' => 0,
            ];
        }

        return $this->availabilityService->getWebsiteAgentStats($website);
    }

    protected function liveChatWebsiteForAgent($agent): ?Website
    {
        return $agent->assignedWebsites()
            ->where('websites.company_id', $agent->company_id)
            ->whereHas('settings', fn ($query) =>
                $query->where('enable_live_chat', true)
            )
            ->with('settings')
            ->orderBy('websites.name')
            ->first();
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
