@extends('agent.layout', ['title' => 'Dashboard'])

@section('content')
    <div class="grid cards">
        <div class="card">
            <div class="muted">Availability</div>
            <div class="value" style="font-size: 24px;">{{ ucfirst($availabilityStatus) }}</div>
            <div class="item-sub">
                {{ $availabilityStats['available'] }} available of {{ $availabilityStats['total'] }} agents
            </div>
        </div>

        <a class="card" href="{{ route('agent.waiting') }}">
            <div class="muted">Waiting Chats</div>
            <div class="value" data-realtime-refresh="dashboard-waiting-count">{{ $waitingCount }}</div>
        </a>

        <a class="card" href="{{ route('agent.chats') }}">
            <div class="muted">My Active Chats</div>
            <div class="value" data-realtime-refresh="dashboard-active-count">{{ $activeCount }}</div>
            <div class="item-sub">Limit {{ $maxActiveChats }}</div>
        </a>

        <a class="card" href="{{ route('agent.closed') }}">
            <div class="muted">Closed Today</div>
            <div class="value">{{ $closedTodayCount }}</div>
        </a>
    </div>

    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 18px;">
        <section class="panel">
            <div class="panel-header">
                <strong>
                    Waiting Chats
                    <span class="badge waiting" data-realtime-refresh="dashboard-waiting-badge">{{ $waitingCount }}</span>
                </strong>
                <a class="muted" href="{{ route('agent.waiting') }}">View all</a>
            </div>
            <div class="panel-body" data-realtime-refresh="dashboard-waiting">
                <div class="list">
                    @forelse ($waitingConversations as $conversation)
                        @include('agent.partials.conversation-item', [
                            'conversation' => $conversation,
                            'action' => 'accept',
                        ])
                    @empty
                        <div class="muted">No visitors are waiting.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <strong>
                    My Chats
                    <span class="badge active" data-realtime-refresh="dashboard-active-badge">{{ $activeCount }}</span>
                </strong>
                <a class="muted" href="{{ route('agent.chats') }}">View all</a>
            </div>
            <div class="panel-body" data-realtime-refresh="dashboard-active">
                <div class="list">
                    @forelse ($activeConversations as $conversation)
                        @include('agent.partials.conversation-item', [
                            'conversation' => $conversation,
                            'action' => 'open',
                        ])
                    @empty
                        <div class="muted">No active chats assigned to you.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
