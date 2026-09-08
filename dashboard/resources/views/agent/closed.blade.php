@extends('agent.layout', ['title' => 'Closed Chats'])

@section('content')
    <section class="panel">
        <div class="panel-header">
            <strong>Closed Live Chats</strong>
        </div>
        <div class="panel-body" data-realtime-refresh="closed-list">
            <div class="list">
                @forelse ($sessions as $session)
                    @php
                        $conversation = $session->conversation;
                        $visitor = $conversation?->visitor?->visitor_uuid
                            ? 'Visitor ' . substr($conversation->visitor->visitor_uuid, 0, 8)
                            : 'Unknown Visitor';
                        $lastMessage = $conversation?->messages()->latest('id')->value('message');
                        $closedBy = $session->ended_by === 'visitor'
                            ? 'Closed by Visitor'
                            : 'Closed by Agent';
                        $hasRating = $session->rating_status === 'submitted' && $session->rating;
                        $rating = $hasRating
                            ? str_repeat('★', $session->rating) . ' ' . $session->rating . '/5'
                            : 'Not Rated';
                        $ratingClass = match (true) {
                            ! $hasRating => 'empty',
                            $session->rating <= 2 => 'danger',
                            $session->rating === 3 => 'warning',
                            default => 'success',
                        };
                    @endphp

                    <div class="item">
                        <div class="item-main">
                            <div class="item-title">{{ $visitor }}</div>
                            <div class="item-sub">{{ $conversation?->website?->name ?? 'Unknown website' }}</div>
                            @if ($lastMessage)
                                <div class="item-sub">{{ \Illuminate\Support\Str::limit($lastMessage, 90) }}</div>
                            @endif
                            <div class="item-sub">{{ $closedBy }}</div>
                        </div>

                        <div class="closed-list-actions">
                            <div class="closed-list-badges">
                                <span class="rating-badge {{ $ratingClass }}">{{ $rating }}</span>
                                <span class="badge closed">Closed</span>
                            </div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: end;">
                                <a class="btn gray" href="{{ route('agent.closed.show', $session) }}">Open</a>
                                <a
                                    class="btn {{ filled($session->note) ? 'note-added' : 'note-required' }}"
                                    href="{{ route('agent.closed.note.edit', $session) }}"
                                >
                                    {{ filled($session->note) ? 'Note Added' : 'Add Note' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="muted">No closed live chats.</div>
                @endforelse
            </div>

            <div class="pagination" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <div class="muted">
                    Showing {{ $sessions->firstItem() ?? 0 }} to {{ $sessions->lastItem() ?? 0 }} of {{ $sessions->total() }} closed chats
                </div>

                <div style="display: flex; gap: 8px;">
                    @if ($sessions->onFirstPage())
                        <span class="btn gray" style="cursor: not-allowed; opacity: .55;">Previous</span>
                    @else
                        <a class="btn gray" href="{{ $sessions->previousPageUrl() }}">Previous</a>
                    @endif

                    @if ($sessions->hasMorePages())
                        <a class="btn primary" href="{{ $sessions->nextPageUrl() }}">Next</a>
                    @else
                        <span class="btn gray" style="cursor: not-allowed; opacity: .55;">Next</span>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
