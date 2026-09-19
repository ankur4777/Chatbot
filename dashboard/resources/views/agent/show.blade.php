@extends('agent.layout', ['title' => 'Conversation #' . $conversation->id])

@section('content')
    @php
        $displayStatus = isset($liveChatSession)
            ? 'closed'
            : $conversation->status;

        $statusClass = match ($displayStatus) {
            'live_active' => 'live',
            'closed', 'resolved', 'ended' => 'closed',
            'waiting_agent' => 'waiting',
            default => 'default',
        };

        $statusLabel = match ($displayStatus) {
            'live_active' => 'Live Active',
            'waiting_agent' => 'Waiting',
            default => ucfirst(str_replace('_', ' ', $displayStatus)),
        };

        $ratingLabel = isset($liveChatSession)
            && $liveChatSession->rating_status === 'submitted'
            && $liveChatSession->rating
                ? str_repeat('*', $liveChatSession->rating) . ' ' . $liveChatSession->rating . '/5'
                : null;

        $agentChatStatus = ($activeLiveChatSession ?? $conversation->activeLiveChatSession)?->agent_chat_status
            ?: \App\Models\LiveChatSession::AGENT_CHAT_STATUS_ACTIVE;
        $agentChatStatusLabels = \App\Models\LiveChatSession::agentChatStatusLabels();
        $agentChatStatusLabel = $agentChatStatusLabels[$agentChatStatus] ?? 'Active';
        $visitorUuid = $conversation->visitor?->visitor_uuid ?? 'unknown';
        $visitorLabel = $conversation->visitor?->displayName() ?? 'Unknown Visitor';
        $visitorAvatar = strtoupper($conversation->visitor?->initials() ?: 'UV');
        $closedDuration = isset($liveChatSession) && $liveChatSession->started_at && $liveChatSession->ended_at
            ? $liveChatSession->started_at->diffForHumans($liveChatSession->ended_at, true)
            : 'N/A';
        $totalMessages = $conversation->messages->count();
        $visitorMessages = $conversation->messages->whereIn('sender_type', ['visitor', 'user'])->count();
        $agentMessages = $conversation->messages->where('sender_type', 'agent')->count();
        $attachmentCount = $conversation->messages->filter(fn ($message) => filled($message->attachment))->count();
        $voiceNoteCount = $conversation->messages->filter(fn ($message) => filled($message->attachment) && $message->attachment_type === 'audio')->count();
    @endphp

    <div class="chat-layout">
        <button class="chat-list-toggle" type="button" data-chat-list-toggle aria-label="Open chats" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            Chats
        </button>
        <button class="chat-list-backdrop" type="button" data-chat-list-backdrop aria-label="Close chats" hidden></button>

        <aside class="panel conversation-list" data-chat-list-panel>
            <div class="panel-header">
                <strong>Waiting Chats</strong>
            </div>

            <div data-realtime-refresh="chat-waiting-list">
                @forelse ($waitingConversations as $waitingConversation)
                    @include('agent.partials.conversation-item', [
                        'conversation' => $waitingConversation,
                        'action' => 'accept',
                    ])
                @empty
                    <div class="panel-body muted">No waiting chats.</div>
                @endforelse
            </div>

            <div class="panel-header conversation-section-header">
                <strong>My Chats</strong>
            </div>

            <div data-realtime-refresh="chat-active-list">
                @forelse ($conversationList as $listConversation)
                    @php
                        $listVisitorUuid = $listConversation->visitor?->visitor_uuid ?? 'unknown';
                        $listVisitorLabel = $listConversation->visitor?->displayName() ?? 'Unknown Visitor';
                        $listAvatar = strtoupper($listConversation->visitor?->initials() ?: 'UV');
                        $listStatus = $listConversation->activeLiveChatSession?->agent_chat_status
                            ?: \App\Models\LiveChatSession::AGENT_CHAT_STATUS_ACTIVE;
                        $listStatusLabel = \App\Models\LiveChatSession::agentChatStatusLabels()[$listStatus] ?? 'Active';
                        $listLastVisitorMessage = $listConversation->messages()
                            ->whereIn('sender_type', ['visitor', 'user'])
                            ->latest('id')
                            ->first(['message', 'created_at']);
                    @endphp
                    <a
                        href="{{ route('agent.chats.show', $listConversation) }}"
                        @class([
                            'conversation-link',
                            'active' => $listConversation->id === $conversation->id,
                        ])
                        data-agent-live-chat-link
                        data-conversation-id="{{ $listConversation->id }}"
                        data-visitor-label="{{ $listVisitorLabel }}"
                    >
                        <span class="conversation-avatar">{{ $listAvatar }}</span>
                        <div class="conversation-link-main">
                            <div class="item-title">{{ $listVisitorLabel }}</div>
                            <div class="item-sub">{{ $listConversation->website?->name ?? 'Unknown website' }}</div>
                            @if ($listLastVisitorMessage)
                                <div class="conversation-last-message">
                                    {{ \Illuminate\Support\Str::limit($listLastVisitorMessage->message, 54) }}
                                </div>
                            @endif
                        </div>
                        <div class="conversation-link-meta">
                            <span
                                class="agent-chat-status-badge {{ $listStatus }}"
                                data-agent-chat-status-badge="{{ $listConversation->id }}"
                            >
                                {{ $listStatusLabel }}
                            </span>
                            @if ($listLastVisitorMessage?->created_at)
                                <time datetime="{{ $listLastVisitorMessage->created_at->toIso8601String() }}">
                                    {{ \App\Support\BrowserTime::format($listLastVisitorMessage->created_at, 'h:i A') }}
                                </time>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="panel-body muted">No active chats.</div>
                @endforelse
            </div>
        </aside>

        @isset($liveChatSession)
            <div class="closed-conversation-layout">
                <div class="closed-main-column">
        @endisset

        <section class="panel chat-panel" data-conversation-id="{{ $conversation->id }}">
            <div class="panel-header chat-conversation-header">
                @isset($liveChatSession)
                    <div class="closed-chat-heading">
                        <a class="closed-back-btn" href="{{ route('agent.closed') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M15 18 9 12l6-6M10 12h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Back
                        </a>
                        <div class="closed-title-block">
                            <div class="closed-title-line">
                                <strong>Conversation #{{ $conversation->id }}</strong>
                                <span class="closed-lock" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M7 11V8a5 5 0 0 1 10 0v3M6 11h12v10H6z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="closed-state-badge">Closed</span>
                            </div>
                            <div>{{ $visitorLabel }} &bull; {{ $conversation->website?->name ?? 'Unknown website' }}</div>
                        </div>
                    </div>
                    <div class="closed-download-actions">
                        <a class="closed-download-btn" href="{{ route('agent.closed.download', $liveChatSession) }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3v11m0 0 4-4m-4 4-4-4M5 20h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Download PDF
                        </a>
                    </div>
                @else
                <div class="chat-conversation-info">
                    <span class="chat-conversation-avatar" aria-hidden="true">{{ $visitorAvatar }}</span>
                    <div class="chat-conversation-copy">
                    <strong class="chat-conversation-title">
                        {{ $visitorLabel }}
                    </strong>
                    <div class="chat-conversation-meta">
                        {{ $conversation->website?->name ?? 'Unknown website' }}
                        · <span
                            class="status-pill {{ $statusClass }}"
                            data-conversation-status
                        >
                            {{ $statusLabel }}
                        </span>
                        @isset($liveChatSession)
                            · {{ $liveChatSession->ended_by === 'visitor' ? 'Closed by Visitor' : 'Closed by Agent' }}
                        @endisset
                        @isset($liveChatSession)
                            <span>Rating: {{ $ratingLabel ?? 'Not Rated' }}</span>
                        @endisset
                    </div>
                    </div>
                    @isset($liveChatSession)
                        @if ($liveChatSession->rating_status === 'submitted' && filled($liveChatSession->feedback))
                            <div class="item-sub">Feedback: {{ $liveChatSession->feedback }}</div>
                        @endif
                    @endisset
                </div>

                @if (! isset($liveChatSession) && $conversation->status === 'live_active')
                    <div class="chat-header-actions">
                        <button class="btn gray visitor-edit-trigger" type="button" data-visitor-modal-open>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="m16 14 4 4m0-4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            Edit Visitor
                        </button>
                        <form
                            class="agent-chat-status-control {{ $agentChatStatus }}"
                            method="POST"
                            action="{{ route('agent.chats.status', $conversation) }}"
                            data-agent-chat-status-form
                        >
                            @csrf
                            <label>Status</label>
                            <input
                                type="hidden"
                                name="agent_chat_status"
                                value="{{ $agentChatStatus }}"
                                data-agent-chat-status-input
                                data-conversation-id="{{ $conversation->id }}"
                            >
                            <div class="agent-chat-status-menu-wrap">
                                <button
                                    class="agent-chat-status-trigger"
                                    type="button"
                                    data-agent-chat-status-trigger
                                    aria-haspopup="listbox"
                                    aria-expanded="false"
                                >
                                    <span class="agent-chat-status-dot" aria-hidden="true"></span>
                                    <span class="agent-chat-status-current" data-agent-chat-status-current>{{ $agentChatStatusLabel }}</span>
                                    <span class="agent-chat-status-chevron">⌄</span>
                                </button>
                                <div class="agent-chat-status-menu" data-agent-chat-status-menu hidden role="listbox">
                                    @foreach ($agentChatStatusLabels as $value => $label)
                                        @php
                                            $statusDescriptions = [
                                                \App\Models\LiveChatSession::AGENT_CHAT_STATUS_ACTIVE => 'Chat is active and ongoing',
                                                \App\Models\LiveChatSession::AGENT_CHAT_STATUS_ON_HOLD => 'Chat is on hold temporarily',
                                                \App\Models\LiveChatSession::AGENT_CHAT_STATUS_AWAITING_VISITOR => 'Waiting for visitor response',
                                            ];
                                        @endphp
                                        <button
                                            class="agent-chat-status-option {{ $value }}"
                                            type="button"
                                            data-agent-chat-status-option="{{ $value }}"
                                            data-agent-chat-status-label="{{ $label }}"
                                            role="option"
                                            aria-selected="{{ $agentChatStatus === $value ? 'true' : 'false' }}"
                                        >
                                            <span class="agent-chat-status-option-text">
                                                <strong>{{ $label }}</strong>
                                                <small>{{ $statusDescriptions[$value] ?? 'Update chat status' }}</small>
                                            </span>
                                            <svg class="agent-chat-status-check" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                <path d="M16 5L7.25 13.75L4 10.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="agent-chat-status-error" data-agent-chat-status-error hidden></div>
                        </form>

                        @if (isset($noteSession) && $noteSession)
                            <a
                                class="btn {{ filled($noteSession->note) ? 'note-added' : 'note-required' }}"
                                href="{{ route('agent.closed.note.edit', $noteSession) }}"
                            >
                                {{ filled($noteSession->note) ? 'Note Added' : 'Add Note' }}
                            </a>
                        @endif

                        <form method="POST" action="{{ route('agent.chats.close', $conversation) }}">
                            @csrf
                            <button class="btn danger" type="submit">Close Chat</button>
                        </form>
                    </div>
                @endif
                @endisset
            </div>

            <div class="messages" data-message-list>
                @forelse ($conversation->messages as $message)
                    @php
                        $sender = match ($message->sender_type) {
                            'visitor', 'user' => 'visitor',
                            'bot', 'assistant', 'ai' => 'bot',
                            'agent' => 'agent',
                            default => 'system',
                        };

                        $label = match ($sender) {
                            'visitor' => 'Visitor',
                            'bot' => 'AI Assistant',
                            'agent' => $message->user?->name ?? 'Agent',
                            default => 'System',
                        };

                        $attachment = $message->attachment
                            ? [
                                'name' => $message->metadata['attachment']['original_name'] ?? basename($message->attachment),
                                'type' => $message->attachment_type,
                                'url' => route('agent.chats.attachments.show', [$conversation, $message]),
                            ]
                            : null;
                    @endphp

                    <div
                        class="message {{ $sender }}"
                        data-message-id="{{ $message->id }}"
                        data-created-at="{{ optional($message->created_at)->toIso8601String() }}"
                    >
                        <div class="bubble">
                            <div class="bubble-meta">
                                {{ $label }} · {{ optional($message->created_at)->format('d M, h:i A') }}
                            </div>
                            @if (filled($message->message))
                                <div class="bubble-text">{{ $message->message }}</div>
                            @endif

                            @if ($attachment)
                                <div class="message-attachment">
                                    @if ($attachment['type'] === 'image')
                                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener">
                                            <img src="{{ $attachment['url'] }}" alt="{{ $attachment['name'] }}">
                                        </a>
                                    @elseif ($attachment['type'] === 'video')
                                        <video controls preload="metadata">
                                            <source src="{{ $attachment['url'] }}">
                                            Your browser does not support video playback.
                                        </video>
                                    @elseif ($attachment['type'] === 'audio')
                                        <div class="voice-player" data-voice-player data-duration="{{ $attachment['duration'] ?? '' }}">
                                            <audio class="voice-audio" src="{{ $attachment['url'] }}" preload="metadata"></audio>
                                            <button class="voice-play" type="button" aria-label="Play voice note">▶</button>
                                            <button class="voice-track" type="button" aria-label="Seek voice note">
                                                <span class="voice-progress"></span>
                                                <span class="voice-bars">
                                                    @for ($index = 0; $index < 18; $index++)
                                                        <span style="--bar-height: {{ 8 + (($index * 7) % 18) }}px"></span>
                                                    @endfor
                                                </span>
                                            </button>
                                            <span class="voice-time">0:00</span>
                                        </div>
                                    @else
                                        <a class="attachment-card" href="{{ $attachment['url'] }}" target="_blank" rel="noopener">
                                            <span class="attachment-icon">PDF</span>
                                            <span>{{ $attachment['name'] }}</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="muted">No messages yet.</div>
                @endforelse
            </div>

            <button class="chat-new-messages" type="button" data-new-messages-button hidden>
                <span data-new-messages-count>1</span>
                new message
            </button>

            <div class="composer" data-message-composer>
                @if (! isset($liveChatSession) && $conversation->status === 'live_active')
                    <div class="typing-row" data-visitor-typing hidden>
                        Visitor is typing...
                    </div>

                    @if ($cannedReplies->isNotEmpty())
                        <div class="canned-replies">
                            @foreach ($cannedReplies as $reply)
                                <button
                                    class="btn gray"
                                    type="button"
                                    data-canned-reply="{{ e($reply->message) }}"
                                >
                                    {{ $reply->title }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="agent-recording-row" data-agent-recording-area hidden>
                        <span data-agent-recording-status>Recording 00:00</span>
                        <button class="btn gray" type="button" data-agent-recording-cancel>Cancel</button>
                        <button class="btn primary" type="button" data-agent-recording-send>Send</button>
                    </div>

                    <form method="POST" action="{{ route('agent.chats.messages', $conversation) }}" enctype="multipart/form-data" data-agent-message-form>
                        @csrf
                        <input
                            type="file"
                            name="attachment"
                            data-agent-attachment
                            accept="image/jpeg,image/png,image/webp,application/pdf,video/mp4,video/webm,video/quicktime,video/ogg,audio/webm,audio/ogg,audio/mpeg,audio/mp4,audio/x-m4a,audio/m4a"
                            hidden
                        >
                        <input type="hidden" name="attachment_duration" data-agent-attachment-duration>
                        <textarea
                            name="message"
                            maxlength="5000"
                            placeholder="Type your reply..."
                            data-agent-composer-textarea
                        >{{ old('message') }}</textarea>
                        <div class="agent-attachment-row" data-agent-attachment-row hidden>
                            <span data-agent-attachment-name></span>
                            <button class="btn gray" type="button" data-agent-attachment-clear>Remove</button>
                        </div>
                        <div class="composer-actions">
                            <button class="btn gray" type="button" data-agent-attach>
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m21 12-8.5 8.5a5 5 0 0 1-7-7L14 5a3 3 0 0 1 4 4l-8.5 8.5a1 1 0 0 1-1.5-1.5L16 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Attach
                            </button>
                            <button class="btn gray" type="button" data-agent-mic>
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v5a3 3 0 0 0 3 3Zm7-3a7 7 0 0 1-14 0M12 18v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Voice
                            </button>
                            <button class="btn primary" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m22 2-7 20-4-9-9-4 20-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Send
                            </button>
                        </div>
                    </form>
                @else
                    <div class="closed-conversation-message">
                        {{ isset($liveChatSession) ? 'This live chat session is closed.' : 'This conversation is closed.' }}
                    </div>
                @endif
            </div>
        </section>

        @isset($liveChatSession)
                <section class="panel closed-summary-panel">
                    <div class="panel-header">
                        <strong class="closed-summary-title">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 19V11M12 19V5M19 19v-8" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            </svg>
                            Conversation Summary
                        </strong>
                    </div>
                    <div class="panel-body closed-summary-grid">
                        <div class="closed-summary-card">
                            <span class="closed-summary-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8"/></svg></span>
                            <div><span>Total Messages</span><strong>{{ $totalMessages }}</strong></div>
                        </div>
                        <div class="closed-summary-card">
                            <span class="closed-summary-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                            <div><span>Visitor Messages</span><strong>{{ $visitorMessages }}</strong></div>
                        </div>
                        <div class="closed-summary-card">
                            <span class="closed-summary-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                            <div><span>Agent Messages</span><strong>{{ $agentMessages }}</strong></div>
                        </div>
                        <div class="closed-summary-card">
                            <span class="closed-summary-icon"><svg viewBox="0 0 24 24" fill="none"><path d="m21 12-8.5 8.5a5 5 0 0 1-7-7L14 5a3 3 0 0 1 4 4l-8.5 8.5a1 1 0 0 1-1.5-1.5L16 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                            <div><span>Attachments</span><strong>{{ $attachmentCount }}</strong></div>
                        </div>
                        <div class="closed-summary-card">
                            <span class="closed-summary-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v5a3 3 0 0 0 3 3Zm7-3a7 7 0 0 1-14 0M12 18v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                            <div><span>Voice Notes</span><strong>{{ $voiceNoteCount }}</strong></div>
                        </div>
                    </div>
                </section>
                </div>
            <aside class="panel closed-detail-panel">
                <div class="panel-header">
                    <strong class="closed-section-title">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        Conversation Details
                    </strong>
                </div>
                <div class="panel-body closed-detail-list">
                    <div class="closed-detail-row">
                        <span>Visitor</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                        <strong>{{ $visitorLabel }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Phone</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M8 5 6 7c-1 1-1 3 0 5a17 17 0 0 0 6 6c2 1 4 1 5 0l2-2-4-4-2 2c-2-1-3-2-4-4l2-2-3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <strong>{{ $conversation->visitor?->phone ?? '-' }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Email</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></span>
                        <strong>{{ $conversation->visitor?->email ?? '-' }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Website</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18" stroke="currentColor" stroke-width="1.8"/></svg></span>
                        <strong>{{ $conversation->website?->name ?? 'Unknown website' }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Started At</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v4M17 3v4M4 8h16M5 5h14v15H5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <strong>{{ optional($liveChatSession->started_at)->format('d M Y, h:i A') ?? 'N/A' }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Closed At</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3v4M17 3v4M4 8h16M5 5h14v15H5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <strong>{{ optional($liveChatSession->ended_at)->format('d M Y, h:i A') ?? 'N/A' }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Duration</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                        <strong>{{ $closedDuration }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Closed By</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9a7 7 0 0 0-14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                        <strong>{{ $liveChatSession->ended_by === 'visitor' ? 'Visitor' : 'Agent' }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Rating</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></span>
                        <strong>{{ $ratingLabel ?? 'Not Rated' }}</strong>
                    </div>
                    <div class="closed-detail-row">
                        <span>Feedback</span>
                        <span class="closed-detail-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8"/></svg></span>
                        <strong>{{ $liveChatSession->feedback ?: '-' }}</strong>
                    </div>
                    <button class="btn gray visitor-edit-trigger" type="button" data-visitor-modal-open>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="m16 14 4 4m0-4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        Edit Visitor
                    </button>
                </div>

                <div class="panel-header closed-note-header">
                    <strong class="closed-section-title">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Agent Note
                    </strong>
                </div>
                <div class="panel-body">
                    <div class="closed-note-box">{{ $liveChatSession->note ?: 'No notes added.' }}</div>
                </div>
            </aside>
            </div>
        @endisset
    </div>

    <div class="visitor-modal-backdrop" data-visitor-modal hidden>
        <div class="visitor-modal" role="dialog" aria-modal="true" aria-labelledby="visitor-modal-title">
            <form method="POST" action="{{ route('agent.chats.visitor.update', $conversation) }}">
                @csrf
                @method('PATCH')
                <div class="visitor-modal-header">
                    <div>
                        <h2 id="visitor-modal-title">Edit Visitor Details</h2>
                        <p>{{ $conversation->website?->name ?? 'Unknown website' }} &bull; Conversation #{{ $conversation->id }}</p>
                    </div>
                    <button type="button" class="visitor-modal-close" data-visitor-modal-close aria-label="Close visitor details">
                        &times;
                    </button>
                </div>
                <div class="visitor-modal-body">
                    @if ($errors->any())
                        <div class="visitor-modal-error">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <label>
                        <span>Name <strong>*</strong></span>
                        <input name="name" type="text" maxlength="255" required value="{{ old('name', $conversation->visitor?->name) }}" placeholder="Visitor name">
                    </label>
                    <label>
                        <span>Email</span>
                        <input name="email" type="email" maxlength="255" value="{{ old('email', $conversation->visitor?->email) }}" placeholder="visitor@example.com">
                    </label>
                    <label>
                        <span>Phone Number</span>
                        <input name="phone" type="text" maxlength="20" value="{{ old('phone', $conversation->visitor?->phone) }}" placeholder="+91 9876543210">
                    </label>
                    @if ($conversation->visitor?->details_updated_at)
                        <div class="visitor-modal-audit">
                            Last updated {{ $conversation->visitor->details_updated_at->format('d M Y, h:i A') }}
                            @if ($conversation->visitor->detailsUpdatedBy?->name)
                                by {{ $conversation->visitor->detailsUpdatedBy->name }}
                            @endif
                        </div>
                    @endif
                </div>
                <div class="visitor-modal-actions">
                    <button type="button" class="btn gray" data-visitor-modal-close>Cancel</button>
                    <button type="submit" class="btn primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection
