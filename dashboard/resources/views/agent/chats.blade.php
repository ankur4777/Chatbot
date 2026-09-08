@extends('agent.layout', ['title' => 'My Chats'])

@section('content')
    <section class="panel">
        <div class="panel-header">
            <strong>Active Conversations Assigned to You</strong>
        </div>
        <div class="panel-body" data-realtime-refresh="active-list">
            <div class="list">
                @forelse ($conversations as $conversation)
                    @include('agent.partials.conversation-item', [
                        'conversation' => $conversation,
                        'action' => 'open',
                    ])
                @empty
                    <div class="muted">No active chats assigned to you.</div>
                @endforelse
            </div>

            <div class="pagination">
                {{ $conversations->links() }}
            </div>
        </div>
    </section>
@endsection
