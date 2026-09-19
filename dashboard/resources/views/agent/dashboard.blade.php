@extends('agent.layout', ['title' => 'Dashboard'])

@section('content')
    <style>
        .agent-dashboard-page {
            display: grid;
            gap: 18px;
            padding-top: clamp(36px, 3.4vw, 50px);
        }
        .dashboard-hero h1 {
            color: #07142f;
            font-size: clamp(22px, 1.7vw, 29px);
            font-weight: 720;
            letter-spacing: 0;
            line-height: 1.12;
            margin: 0 0 8px;
        }
        .dashboard-hero p {
            color: #415475;
            font-size: clamp(13px, .9vw, 15px);
            font-weight: 500;
            margin: 0;
        }
        .dashboard-stat-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .dashboard-stat-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dce6f3;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
            color: inherit;
            display: grid;
            gap: 16px;
            grid-template-columns: 52px minmax(0, 1fr) auto;
            min-height: 112px;
            padding: 16px 18px;
            text-decoration: none;
        }
        .dashboard-stat-card.availability { background: linear-gradient(135deg, #f4fff8 0%, #fff 75%); }
        .dashboard-stat-card.waiting { background: linear-gradient(135deg, #f7fbff 0%, #fff 75%); }
        .dashboard-stat-card.active { background: linear-gradient(135deg, #fbf7ff 0%, #fff 75%); }
        .dashboard-stat-card.closed { background: linear-gradient(135deg, #fff8f1 0%, #fff 75%); }
        .dashboard-stat-icon {
            align-items: center;
            border-radius: 16px;
            display: inline-flex;
            height: 52px;
            justify-content: center;
            width: 52px;
        }
        .dashboard-stat-icon svg { height: 25px; width: 25px; }
        .dashboard-stat-card.availability .dashboard-stat-icon { background: #dcfce7; color: #16a34a; }
        .dashboard-stat-card.waiting .dashboard-stat-icon { background: #dbeafe; color: #2563eb; }
        .dashboard-stat-card.active .dashboard-stat-icon { background: #ede9fe; color: #6d28d9; }
        .dashboard-stat-card.closed .dashboard-stat-icon { background: #ffedd5; color: #ea580c; }
        .dashboard-stat-label {
            color: #243654;
            font-size: 13px;
            font-weight: 650;
            line-height: 1.2;
        }
        .dashboard-stat-value {
            color: #07142f;
            font-size: 26px;
            font-weight: 760;
            line-height: 1;
            margin-top: 10px;
        }
        .dashboard-stat-card.availability .dashboard-stat-value { color: #16a34a; }
        .dashboard-stat-card.active .dashboard-stat-value { color: #5b21b6; }
        .dashboard-stat-card.closed .dashboard-stat-value { color: #ea580c; }
        .dashboard-stat-note {
            color: #526381;
            font-size: 12px;
            font-weight: 500;
            margin-top: 8px;
        }
        .dashboard-stat-trend {
            align-items: center;
            align-self: start;
            background: #dcfce7;
            border-radius: 999px;
            color: #16a34a;
            display: inline-flex;
            height: 30px;
            justify-content: center;
            width: 30px;
        }
        .dashboard-stat-trend svg { height: 17px; width: 17px; }
        .dashboard-panel-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }
        .dashboard-panel {
            background: #fff;
            border: 1px solid #dce6f3;
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
            min-height: 510px;
            overflow: hidden;
        }
        .dashboard-panel-header {
            align-items: center;
            border-bottom: 1px solid #dce6f3;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 16px 20px;
        }
        .dashboard-panel-title {
            align-items: center;
            color: #07142f;
            display: inline-flex;
            font-size: 20px;
            font-weight: 700;
            gap: 10px;
            line-height: 1.2;
        }
        .dashboard-count {
            align-items: center;
            background: #dcfce7;
            border-radius: 999px;
            color: #16a34a;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            height: 28px;
            justify-content: center;
            min-width: 28px;
            padding: 0 9px;
        }
        .dashboard-count.waiting {
            background: #fef3c7;
            color: #b45309;
        }
        .dashboard-panel-link {
            align-items: center;
            color: #2563eb;
            display: inline-flex;
            font-size: 14px;
            font-weight: 650;
            gap: 7px;
            text-decoration: none;
            white-space: nowrap;
        }
        .dashboard-panel-link.muted-link { color: #64748b; }
        .dashboard-panel-link svg { height: 17px; width: 17px; }
        .dashboard-panel-body {
            padding: 16px 20px 20px;
        }
        .dashboard-empty-state {
            align-items: center;
            color: #526381;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 310px;
            padding: 22px;
            text-align: center;
        }
        .dashboard-empty-state svg {
            color: #c4cfdd;
            height: 76px;
            margin-bottom: 16px;
            width: 76px;
        }
        .dashboard-empty-state strong {
            color: #07142f;
            display: block;
            font-size: 17px;
            font-weight: 720;
            margin-bottom: 9px;
        }
        .dashboard-empty-state p {
            font-size: 14px;
            line-height: 1.45;
            margin: 0;
            max-width: 360px;
        }
        .dashboard-chat-list {
            display: grid;
            gap: 12px;
        }
        .dashboard-chat-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dce6f3;
            border-radius: 8px;
            color: inherit;
            display: grid;
            gap: 12px;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            padding: 12px;
            text-decoration: none;
        }
        .dashboard-chat-avatar {
            align-items: center;
            background: #f3e8ff;
            border-radius: 999px;
            color: #6d28d9;
            display: inline-flex;
            font-size: 16px;
            font-weight: 800;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .dashboard-chat-main { min-width: 0; }
        .dashboard-chat-title {
            align-items: center;
            color: #07142f;
            display: flex;
            flex-wrap: wrap;
            font-size: 15px;
            font-weight: 720;
            gap: 8px;
            line-height: 1.25;
        }
        .dashboard-chat-website {
            color: #526381;
            font-size: 13px;
            font-weight: 500;
            margin-top: 4px;
        }
        .dashboard-chat-message {
            color: #243654;
            font-size: 13px;
            font-weight: 500;
            margin-top: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .dashboard-chat-meta {
            align-items: flex-end;
            display: grid;
            gap: 10px;
            justify-items: end;
        }
        .dashboard-live-pill {
            align-items: center;
            background: #dcfce7;
            border-radius: 999px;
            color: #16a34a;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            gap: 6px;
            padding: 7px 10px;
            white-space: nowrap;
        }
        .dashboard-live-pill::before {
            background: currentColor;
            border-radius: 999px;
            content: "";
            height: 8px;
            width: 8px;
        }
        .dashboard-chat-time {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }
        .dashboard-open-btn,
        .dashboard-accept-btn {
            align-items: center;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            gap: 8px;
            justify-content: center;
            min-height: 40px;
            padding: 0 18px;
            text-decoration: none;
            white-space: nowrap;
        }
        .dashboard-open-btn {
            background: #eef2f7;
            color: #07142f;
        }
        .dashboard-accept-btn {
            background: #2563eb;
            color: #fff;
        }
        .dashboard-open-btn svg,
        .dashboard-accept-btn svg { height: 16px; width: 16px; }
        @media (max-width: 1500px) {
            .dashboard-stat-grid { gap: 14px; }
            .dashboard-stat-card {
                grid-template-columns: 48px minmax(0, 1fr) auto;
                min-height: 108px;
                padding: 16px;
            }
            .dashboard-stat-icon {
                height: 48px;
                width: 48px;
            }
            .dashboard-stat-icon svg {
                height: 23px;
                width: 23px;
            }
        }
        @media (max-width: 1280px) {
            .dashboard-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .dashboard-panel-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 900px) {
            .agent-dashboard-page { padding-top: 18px; }
        }
        @media (max-width: 640px) {
            .dashboard-stat-grid { grid-template-columns: 1fr; }
            .dashboard-stat-card {
                grid-template-columns: 46px minmax(0, 1fr);
            }
            .dashboard-stat-trend { display: none; }
            .dashboard-panel-header {
                align-items: flex-start;
                flex-direction: column;
            }
            .dashboard-chat-card {
                align-items: start;
                grid-template-columns: 44px minmax(0, 1fr);
            }
            .dashboard-chat-avatar {
                font-size: 16px;
                height: 44px;
                width: 44px;
            }
            .dashboard-chat-meta {
                align-items: start;
                grid-column: 2;
                justify-items: start;
            }
        }
    </style>

    @php
        $agentName = auth()->user()->name ?? 'Agent';
        $availabilityLabel = $availabilityStatus === 'away' ? 'On Break' : ucfirst($availabilityStatus);
    @endphp

    <div class="agent-dashboard-page">
        <header class="dashboard-hero">
            <h1>Welcome back, {{ $agentName }}! <span aria-hidden="true">&#128075;</span></h1>
            <p>Here's what's happening with your support chats today.</p>
        </header>

        <section class="dashboard-stat-grid" aria-label="Dashboard overview">
            <div class="dashboard-stat-card availability">
                <span class="dashboard-stat-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.5 21a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                    </svg>
                </span>
                <div>
                    <div class="dashboard-stat-label">Availability</div>
                    <div class="dashboard-stat-value">{{ $availabilityLabel }}</div>
                    <div class="dashboard-stat-note">{{ $availabilityStats['available'] }} available of {{ $availabilityStats['total'] }} agents</div>
                </div>
            </div>

            <a class="dashboard-stat-card waiting" href="{{ route('agent.waiting') }}">
                <span class="dashboard-stat-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 6.5h14v9H9l-4 3.5V6.5Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                        <path d="M8.5 10h7M8.5 13h4" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                    </svg>
                </span>
                <div>
                    <div class="dashboard-stat-label">Waiting Chats</div>
                    <div class="dashboard-stat-value" data-realtime-refresh="dashboard-waiting-count">{{ $waitingCount }}</div>
                    <div class="dashboard-stat-note">{{ $waitingCount > 0 ? 'Visitors need support' : 'No visitors waiting' }}</div>
                </div>
            </a>

            <a class="dashboard-stat-card active" href="{{ route('agent.chats') }}">
                <span class="dashboard-stat-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 7.5A4.5 4.5 0 0 1 9.5 3h5A4.5 4.5 0 0 1 19 7.5v3A4.5 4.5 0 0 1 14.5 15H12l-5 4v-4.35A4.5 4.5 0 0 1 5 10.9V7.5Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <div class="dashboard-stat-label">My Active Chats</div>
                    <div class="dashboard-stat-value" data-realtime-refresh="dashboard-active-count">{{ $activeCount }}</div>
                    <div class="dashboard-stat-note">Limit {{ $maxActiveChats }}</div>
                </div>
            </a>

            <a class="dashboard-stat-card closed" href="{{ route('agent.closed') }}">
                <span class="dashboard-stat-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M7 3h7l4 4v14H7V3Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                        <path d="M14 3v5h5M10 12h5M10 16h5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                    </svg>
                </span>
                <div>
                    <div class="dashboard-stat-label">Closed Today</div>
                    <div class="dashboard-stat-value">{{ $closedTodayCount }}</div>
                    <div class="dashboard-stat-note">{{ $closedTodayCount > 0 ? 'Resolved conversations' : 'No closed chats yet' }}</div>
                </div>
                <span class="dashboard-stat-trend" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="none">
                        <path d="M6 14 14 6M9 6h5v5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </a>
        </section>

        <section class="dashboard-panel-grid">
            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div class="dashboard-panel-title">
                        Waiting Chats
                        <span class="dashboard-count waiting" data-realtime-refresh="dashboard-waiting-badge">{{ $waitingCount }}</span>
                    </div>
                    <a class="dashboard-panel-link" href="{{ route('agent.waiting') }}">
                        View all
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
                <div class="dashboard-panel-body" data-realtime-refresh="dashboard-waiting">
                    @if ($waitingConversations->isEmpty())
                        <div class="dashboard-empty-state">
                            <svg viewBox="0 0 96 96" fill="none" aria-hidden="true">
                                <path d="M30 36h36v24H43L30 72V36Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                <path d="M41 45h18M41 53h10M25 25l-6-8M71 25l6-8M48 22v-9" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                            <strong>No visitors are waiting</strong>
                            <p>All clear! There are no visitors waiting for support at the moment.</p>
                        </div>
                    @else
                        <div class="dashboard-chat-list">
                            @foreach ($waitingConversations as $conversation)
                                @php
                                    $visitorUuid = $conversation->visitor?->visitor_uuid ?? 'unknown';
                                    $visitorLabel = $conversation->visitor?->displayName() ?? 'Unknown Visitor';
                                    $avatar = strtoupper($conversation->visitor?->initials() ?: 'UV');
                                    $lastVisitorMessage = $conversation->messages()
                                        ->whereIn('sender_type', ['visitor', 'user'])
                                        ->latest('id')
                                        ->first(['message', 'created_at']);
                                @endphp
                                <div class="dashboard-chat-card">
                                    <span class="dashboard-chat-avatar">{{ $avatar }}</span>
                                    <div class="dashboard-chat-main">
                                        <div class="dashboard-chat-title">{{ $visitorLabel }}</div>
                                        <div class="dashboard-chat-website">{{ $conversation->website?->name ?? 'Unknown website' }}</div>
                                        <div class="dashboard-chat-message">{{ $lastVisitorMessage ? \Illuminate\Support\Str::limit($lastVisitorMessage->message, 70) : 'Waiting for agent response.' }}</div>
                                    </div>
                                    <div class="dashboard-chat-meta">
                                        @if ($lastVisitorMessage?->created_at)
                                            <time class="dashboard-chat-time" datetime="{{ $lastVisitorMessage->created_at->toIso8601String() }}">
                                                {{ \App\Support\BrowserTime::format($lastVisitorMessage->created_at, 'h:i A') }}
                                            </time>
                                        @endif
                                        <form method="POST" action="{{ route('agent.chats.accept', $conversation) }}">
                                            @csrf
                                            <button class="dashboard-accept-btn" type="submit">Accept</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div class="dashboard-panel-title">
                        My Chats
                        <span class="dashboard-count" data-realtime-refresh="dashboard-active-badge">{{ $activeCount }}</span>
                    </div>
                    <a class="dashboard-panel-link muted-link" href="{{ route('agent.chats') }}">
                        View all
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
                <div class="dashboard-panel-body" data-realtime-refresh="dashboard-active">
                    @if ($activeConversations->isEmpty())
                        <div class="dashboard-empty-state">
                            <svg viewBox="0 0 96 96" fill="none" aria-hidden="true">
                                <path d="M26 34h44v26H42L26 73V34Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                <path d="M39 45h18M39 54h12" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                            <strong>No active chats assigned</strong>
                            <p>New assigned conversations will appear here as soon as visitors connect.</p>
                        </div>
                    @else
                        <div class="dashboard-chat-list">
                            @foreach ($activeConversations as $conversation)
                                @php
                                    $visitorUuid = $conversation->visitor?->visitor_uuid ?? 'unknown';
                                    $visitorLabel = $conversation->visitor?->displayName() ?? 'Unknown Visitor';
                                    $avatar = strtoupper($conversation->visitor?->initials() ?: 'UV');
                                    $agentChatStatus = $conversation->activeLiveChatSession?->agent_chat_status
                                        ?: \App\Models\LiveChatSession::AGENT_CHAT_STATUS_ACTIVE;
                                    $agentChatStatusLabel = \App\Models\LiveChatSession::agentChatStatusLabels()[$agentChatStatus] ?? 'Active';
                                    $lastVisitorMessage = $conversation->messages()
                                        ->whereIn('sender_type', ['visitor', 'user'])
                                        ->latest('id')
                                        ->first(['message', 'created_at']);
                                @endphp
                                <a
                                    class="dashboard-chat-card"
                                    href="{{ route('agent.chats.show', $conversation) }}"
                                    data-agent-live-chat-link
                                    data-conversation-id="{{ $conversation->id }}"
                                    data-visitor-label="{{ $visitorLabel }}"
                                >
                                    <span class="dashboard-chat-avatar">{{ $avatar }}</span>
                                    <div class="dashboard-chat-main">
                                        <div class="dashboard-chat-title">
                                            {{ $visitorLabel }}
                                            <span
                                                class="agent-chat-status-badge {{ $agentChatStatus }}"
                                                data-agent-chat-status-badge="{{ $conversation->id }}"
                                            >
                                                {{ $agentChatStatusLabel }}
                                            </span>
                                        </div>
                                        <div class="dashboard-chat-website">{{ $conversation->website?->name ?? 'Unknown website' }}</div>
                                        <div class="dashboard-chat-message">{{ $lastVisitorMessage ? \Illuminate\Support\Str::limit($lastVisitorMessage->message, 70) : 'No visitor message yet.' }}</div>
                                    </div>
                                    <div class="dashboard-chat-meta">
                                        <span class="dashboard-live-pill">Live Active</span>
                                        @if ($lastVisitorMessage?->created_at)
                                            <time class="dashboard-chat-time" datetime="{{ $lastVisitorMessage->created_at->toIso8601String() }}">
                                                {{ \App\Support\BrowserTime::format($lastVisitorMessage->created_at, 'h:i A') }}
                                            </time>
                                        @endif
                                        <span class="dashboard-open-btn">Open</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

    </div>
@endsection
