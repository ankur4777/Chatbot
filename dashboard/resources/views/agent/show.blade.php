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
    @endphp

    <div class="chat-layout">
        <aside class="panel conversation-list">
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
                    <a
                        href="{{ route('agent.chats.show', $listConversation) }}"
                        @class([
                            'conversation-link',
                            'active' => $listConversation->id === $conversation->id,
                        ])
                    >
                        <div class="item-title">
                            Visitor {{ substr($listConversation->visitor?->visitor_uuid ?? 'unknown', 0, 8) }}
                        </div>
                        <div class="item-sub">{{ $listConversation->website?->name ?? 'Unknown website' }}</div>
                    </a>
                @empty
                    <div class="panel-body muted">No active chats.</div>
                @endforelse
            </div>
        </aside>

        <section class="panel chat-panel" data-conversation-id="{{ $conversation->id }}">
            <div class="panel-header">
                <div>
                    <strong>
                        Visitor {{ substr($conversation->visitor?->visitor_uuid ?? 'unknown', 0, 8) }}
                    </strong>
                    <div class="item-sub">
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
                    @isset($liveChatSession)
                        @if ($liveChatSession->rating_status === 'submitted' && filled($liveChatSession->feedback))
                            <div class="item-sub">Feedback: {{ $liveChatSession->feedback }}</div>
                        @endif
                    @endisset
                </div>

                @if (! isset($liveChatSession) && $conversation->status === 'live_active')
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: end;">
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

                    <form method="POST" action="{{ route('agent.chats.messages', $conversation) }}">
                        @csrf
                        <textarea
                            name="message"
                            maxlength="5000"
                            placeholder="Type your reply..."
                            data-agent-composer-textarea
                            required
                        >{{ old('message') }}</textarea>
                        <div class="composer-actions">
                            <button class="btn primary" type="submit">Send</button>
                        </div>
                    </form>
                @else
                    <div class="closed-conversation-message">
                        {{ isset($liveChatSession) ? 'This live chat session is closed.' : 'This conversation is closed.' }}
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
