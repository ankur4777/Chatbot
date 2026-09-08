@extends('agent.layout', ['title' => 'Internal Note'])

@section('content')
    @php
        $visitor = $conversation?->visitor?->visitor_uuid
            ? 'Visitor ' . substr($conversation->visitor->visitor_uuid, 0, 8)
            : 'Unknown Visitor';
    @endphp

    <section class="panel">
        <div class="panel-header">
            <div>
                <strong>{{ filled($session->note) ? 'View/Edit Note' : 'Add Note' }}</strong>
                <div class="item-sub">
                    {{ $visitor }}
                    &middot; {{ $conversation?->website?->name ?? 'Unknown website' }}
                    @if ($session->ended_at)
                        &middot; Closed {{ optional($session->ended_at)->format('d M, h:i A') }}
                    @else
                        &middot; Live chat active
                    @endif
                </div>
            </div>
            <a
                class="btn gray"
                href="{{ $session->ended_at ? route('agent.closed') : route('agent.chats.show', $conversation) }}"
            >
                Back
            </a>
        </div>

        <div class="panel-body">
            <form method="POST" action="{{ route('agent.closed.note.update', $session) }}" style="display: grid; gap: 14px;">
                @csrf
                @method('PATCH')

                <div>
                    <label for="note" style="display: block; font-weight: 700; margin-bottom: 6px;">
                        Internal Note
                    </label>
                    <textarea
                        id="note"
                        name="note"
                        maxlength="5000"
                        placeholder="Write the visitor's query, solution provided, and any important follow-up..."
                        required
                        style="min-height: 180px;"
                    >{{ old('note', $session->note) }}</textarea>
                    <div class="item-sub">
                        Internal support note. This is not shown to the visitor.
                    </div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button class="btn primary" type="submit">Save Note</button>
                    <a
                        class="btn gray"
                        href="{{ $session->ended_at ? route('agent.closed') : route('agent.chats.show', $conversation) }}"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
