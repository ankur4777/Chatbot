@php
    $visitor = $conversation->visitor?->displayName() ?? 'Unknown Visitor';
    $visitorUuid = $conversation->visitor?->visitor_uuid ?? 'unknown';
    $avatar = strtoupper($conversation->visitor?->initials() ?: substr($visitorUuid, 0, 1) . substr($visitorUuid, -1));

    $lastVisitorMessage = $conversation->messages()
        ->whereIn('sender_type', ['visitor', 'user'])
        ->latest('id')
        ->first(['message', 'created_at']);
    $agentChatStatus = $conversation->activeLiveChatSession?->agent_chat_status
        ?: \App\Models\LiveChatSession::AGENT_CHAT_STATUS_ACTIVE;
    $agentChatStatusLabel = \App\Models\LiveChatSession::agentChatStatusLabels()[$agentChatStatus] ?? 'Active';
@endphp

@if (($action ?? null) === 'open')
    <a
        class="item"
        href="{{ route('agent.chats.show', $conversation) }}"
        @if ($conversation->status === 'live_active')
            data-agent-live-chat-link
            data-conversation-id="{{ $conversation->id }}"
            data-visitor-label="{{ $visitor }}"
        @endif
    >
@else
    <div class="item">
@endif
    <span class="item-avatar" aria-hidden="true">{{ $avatar }}</span>

    <div class="item-main">
        <div class="item-title">
            {{ $visitor }}
            @if ($conversation->status === 'live_active')
                <span
                    class="agent-chat-status-badge {{ $agentChatStatus }}"
                    data-agent-chat-status-badge="{{ $conversation->id }}"
                >
                    {{ $agentChatStatusLabel }}
                </span>
            @endif
        </div>
        <div class="item-sub">{{ $conversation->website?->name ?? 'Unknown website' }}</div>
        @if ($lastVisitorMessage)
            <div class="item-sub item-last-message">
                <span>{{ \Illuminate\Support\Str::limit($lastVisitorMessage->message, 58) }}</span>
                <time datetime="{{ optional($lastVisitorMessage->created_at)->toIso8601String() }}">
                    {{ \App\Support\BrowserTime::format($lastVisitorMessage->created_at, 'h:i A') }}
                </time>
            </div>
        @endif
    </div>

    <div class="item-actions">
        <span @class([
            'badge',
            'waiting' => $conversation->status === 'waiting_agent',
            'active' => $conversation->status === 'live_active',
            'closed' => in_array($conversation->status, ['closed', 'resolved', 'ended'], true),
        ])>
            {{ str_replace('_', ' ', $conversation->status) }}
        </span>

        @if (($action ?? null) === 'accept')
            <form method="POST" action="{{ route('agent.chats.accept', $conversation) }}">
                @csrf
                <button class="btn primary" type="submit">Accept</button>
            </form>
        @elseif (($action ?? null) === 'open')
            <span class="btn gray">Open</span>
        @endif
    </div>
@if (($action ?? null) === 'open')
    </a>
@else
    </div>
@endif
