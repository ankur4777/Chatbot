@extends('agent.layout', ['title' => 'Notifications'])

@section('content')
    <style>
        .notifications-page { display: grid; gap: 18px; padding-top: 22px; }
        .notifications-header { padding-right: 260px; }
        .notifications-header h2 { color: #07142f; font-size: clamp(24px, 2vw, 31px); font-weight: 680; margin: 0; }
        .notifications-header p { color: #526381; font-size: 15px; font-weight: 500; margin: 7px 0 0; }
        .notification-toolbar {
            align-items: center;
            background: #fff;
            border: 1px solid #dce6f3;
            border-radius: 8px;
            display: block;
            gap: 14px;
            padding: 14px;
        }
        .notification-filters { display: flex; flex-wrap: wrap; gap: 8px; }
        .notification-filter {
            background: #f8fafc;
            border: 1px solid #dbe3ef;
            border-radius: 999px;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
            padding: 8px 12px;
            text-decoration: none;
        }
        .notification-filter.active { background: #FF3B30; border-color: #FF3B30; color: #fff; }
        .notification-list { display: grid; gap: 12px; }
        .notification-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dce6f3;
            border-radius: 8px;
            display: grid;
            gap: 14px;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            padding: 14px;
        }
        .notification-card.unread { background: #fff1f0; border-color: #ff9d96; }
        .notification-icon {
            align-items: center;
            border-radius: 12px;
            display: inline-flex;
            font-size: 22px;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .notification-icon.new_visitor_message,
        .notification-icon.visitor_replied,
        .notification-icon.waiting_chat { background: #ffe2df; color: #FF3B30; }
        .notification-icon.follow_up_reminder { background: #fef3c7; color: #d97706; }
        .notification-icon.missed_chat_assigned { background: #fff1f0; color: #DF2F25; }
        .notification-icon.conversation_closed { background: #dcfce7; color: #16a34a; }
        .notification-copy { min-width: 0; }
        .notification-title-row { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
        .notification-title { color: #07142f; font-size: 16px; font-weight: 700; line-height: 1.25; }
        .notification-dot { background: #FF3B30; border-radius: 999px; height: 9px; width: 9px; }
        .notification-message { color: #334155; font-size: 14px; margin-top: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .notification-meta { color: #64748b; display: flex; flex-wrap: wrap; font-size: 12px; font-weight: 600; gap: 8px; margin-top: 8px; }
        .notification-side { align-items: end; display: grid; gap: 8px; justify-items: end; }
        .notification-action-link {
            background: #FF3B30;
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            padding: 9px 13px;
            text-decoration: none;
            white-space: nowrap;
        }
        .notifications-empty {
            background: #fff;
            border: 1px solid #dce6f3;
            border-radius: 8px;
            color: #64748b;
            font-weight: 600;
            padding: 34px;
            text-align: center;
        }
        @media (max-width: 1100px) {
            .notifications-header { padding-right: 0; }
            .notification-card { grid-template-columns: 44px minmax(0, 1fr); }
            .notification-side { grid-column: 2; justify-items: start; }
        }
        @media (max-width: 640px) {
            .notifications-page { padding-top: 18px; }
            .notification-card { align-items: start; grid-template-columns: 42px minmax(0, 1fr); }
            .notification-icon { height: 42px; width: 42px; }
            .notification-copy,
            .notification-side { grid-column: 1 / -1; }
            .notification-message { white-space: normal; }
        }
    </style>

    @php
        $filters = [
            'all' => 'All',
            'visitor_messages' => 'Visitor Messages',
            'follow_up_reminders' => 'Follow-up Reminders',
        ];
        $icons = [
            'new_visitor_message' => '💬',
            'visitor_replied' => '↩',
            'waiting_chat' => '💬',
            'follow_up_reminder' => '⏰',
            'missed_chat_assigned' => '📋',
            'conversation_closed' => '✓',
            'system' => '⚙',
        ];
    @endphp

    <div class="notifications-page">
        <header class="notifications-header">
            <h2>Notifications (Today)</h2>
            <p>Stay updated with visitor activity and follow-up reminders.</p>
        </header>

        <div class="notification-toolbar">
            <nav class="notification-filters" aria-label="Notification filters">
                @foreach ($filters as $value => $label)
                    <a class="notification-filter {{ $activeFilter === $value ? 'active' : '' }}" href="{{ route('agent.notifications', array_filter(['filter' => $value === 'all' ? null : $value, 'search' => $search])) }}">{{ $label }}</a>
                @endforeach
            </nav>
        </div>

            <div class="notification-list">
                @forelse ($notifications as $notification)
                    @php
                        $actionUrl = $notification->data['action_url'] ?? route('agent.notifications');
                        $actionLabel = $notification->data['action_label'] ?? 'Open';
                        $visitor = $notification->conversation?->visitor ?? $notification->missedChat?->visitor;
                        $visitorLabel = $visitor?->displayName() ?? 'Visitor';
                    @endphp
                    <article class="notification-card {{ $notification->is_read ? 'read' : 'unread' }}">
                        <span class="notification-icon {{ $notification->type }}" aria-hidden="true">{{ $icons[$notification->type] ?? '•' }}</span>
                        <div class="notification-copy">
                            <div class="notification-title-row">
                                <span class="notification-title">{{ $notification->title }}</span>
                                @unless ($notification->is_read)
                                    <span class="notification-dot" aria-label="Unread"></span>
                                @endunless
                            </div>
                            <div class="notification-message">{{ $notification->message }}</div>
                            <div class="notification-meta">
                                <span>{{ $notification->website?->name ?? 'Unknown website' }}</span>
                                @if ($notification->conversation_id)
                                    <span>Conversation #{{ $notification->conversation_id }}</span>
                                @endif
                                @if ($notification->missed_chat_id)
                                    <span>Missed #{{ $notification->missed_chat_id }}</span>
                                @endif
                                <time title="{{ $notification->created_at ? \App\Support\BrowserTime::format($notification->created_at, 'd M Y, h:i A') : '' }}">{{ $notification->created_at?->diffForHumans() }}</time>
                            </div>
                        </div>
                        <div class="notification-side">
                            <a class="notification-action-link" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
                        </div>
                    </article>
                @empty
                    <div class="notifications-empty">No notifications found.</div>
                @endforelse
            </div>
        <div class="pagination">{{ $notifications->links() }}</div>
    </div>
@endsection
