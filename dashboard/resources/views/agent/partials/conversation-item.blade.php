@php
    $visitor = $conversation->visitor?->visitor_uuid
        ? 'Visitor ' . substr($conversation->visitor->visitor_uuid, 0, 8)
        : 'Unknown Visitor';

    $lastMessage = $conversation->messages()->latest('id')->value('message');
@endphp

@if (($action ?? null) === 'open')
    <a class="item" href="{{ route('agent.chats.show', $conversation) }}">
@else
    <div class="item">
@endif
    <div class="item-main">
        <div class="item-title">{{ $visitor }}</div>
        <div class="item-sub">{{ $conversation->website?->name ?? 'Unknown website' }}</div>
        @if ($lastMessage)
            <div class="item-sub">{{ \Illuminate\Support\Str::limit($lastMessage, 90) }}</div>
        @endif
    </div>

    <div style="display: grid; gap: 8px; justify-items: end;">
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
