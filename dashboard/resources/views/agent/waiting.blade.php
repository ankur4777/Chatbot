@extends('agent.layout', ['title' => 'Waiting Chats'])

@section('content')
    <section class="panel">
        <div class="panel-header">
            <strong>Visitors Waiting for Support</strong>
            <span class="muted">Active {{ $activeCount }} / {{ $maxActiveChats }}</span>
        </div>
        <div class="panel-body" data-realtime-refresh="waiting-list">
            <div class="list">
                @forelse ($conversations as $conversation)
                    @include('agent.partials.conversation-item', [
                        'conversation' => $conversation,
                        'action' => 'accept',
                    ])
                @empty
                    <div class="muted">No waiting chats.</div>
                @endforelse
            </div>

            <div class="pagination">
                {{ $conversations->links() }}
            </div>
        </div>
    </section>
@endsection
