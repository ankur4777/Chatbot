@extends('agent.layout', ['title' => 'Internal Note'])

@section('content')
    @php
        $visitor = $conversation?->visitor?->displayName() ?? 'Unknown Visitor';
        $visitorInitials = strtoupper($conversation?->visitor?->initials() ?: 'UV');
        $backUrl = $session->ended_at ? route('agent.closed') : route('agent.chats.show', $conversation);
    @endphp

    <style>
        .note-page {
            display: grid;
            gap: 18px;
        }
        .note-page-title h2 {
            color: #0f172a;
            font-size: clamp(22px, 1.7vw, 28px);
            font-weight: 650;
            line-height: 1.08;
            margin: 0;
        }
        .note-page-title p {
            color: #53637c;
            font-size: 14px;
            font-weight: 500;
            margin: 6px 0 0;
        }
        .note-card {
            overflow: hidden;
        }
        .note-visitor-row {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 20px 22px;
        }
        .note-visitor-main {
            align-items: center;
            display: flex;
            gap: 16px;
            min-width: 0;
        }
        .note-visitor-avatar {
            align-items: center;
            background: #ede9fe;
            border-radius: 999px;
            color: #4c1d95;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 18px;
            font-weight: 650;
            height: 56px;
            justify-content: center;
            width: 56px;
        }
        .note-visitor-name {
            color: #0f172a;
            font-size: 18px;
            font-weight: 650;
            line-height: 1.2;
        }
        .note-visitor-meta {
            color: #53637c;
            display: flex;
            flex-wrap: wrap;
            font-size: 13px;
            font-weight: 500;
            gap: 8px;
            margin-top: 6px;
        }
        .note-back {
            align-items: center;
            background: #eef2f7;
            border-radius: 8px;
            color: #0f172a;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 14px;
            font-weight: 650;
            gap: 9px;
            min-height: 40px;
            padding: 0 14px;
        }
        .note-back svg,
        .note-section-title svg,
        .note-save svg {
            height: 16px;
            width: 16px;
        }
        .note-form {
            border-top: 1px solid var(--border);
            display: grid;
            gap: 0;
            padding: 0 22px 22px;
        }
        .note-section-head {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 18px 0 12px;
        }
        .note-section-title {
            align-items: center;
            color: #0f172a;
            display: inline-flex;
            font-size: 17px;
            font-weight: 650;
            gap: 10px;
            line-height: 1.2;
        }
        .note-section-title svg {
            color: #2563eb;
            flex: 0 0 auto;
        }
        .note-help {
            color: #53637c;
            font-size: 12px;
            font-weight: 500;
            text-align: right;
        }
        .note-textarea {
            min-height: 180px;
        }
        .note-count {
            color: #53637c;
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
            text-align: right;
        }
        .note-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }
        .note-save {
            align-items: center;
            gap: 8px;
            min-width: 130px;
        }
        .note-cancel {
            min-width: 92px;
        }
        @media (max-width: 767px) {
            .note-page {
                gap: 16px;
            }
            .note-visitor-row,
            .note-section-head {
                align-items: flex-start;
                flex-direction: column;
            }
            .note-visitor-row,
            .note-form {
                padding-left: 16px;
                padding-right: 16px;
            }
            .note-back,
            .note-actions .btn {
                justify-content: center;
                width: 100%;
            }
            .note-help {
                text-align: left;
            }
            .note-visitor-avatar {
                height: 54px;
                width: 54px;
            }
            .note-visitor-name {
                font-size: 19px;
            }
        }
    </style>

    <div class="note-page">
        <div class="note-page-title">
            <h2>Internal Note</h2>
            <p>Add internal notes for support reference.</p>
        </div>

        <section class="panel note-card">
            <div class="note-visitor-row">
                <div class="note-visitor-main">
                    <span class="note-visitor-avatar">{{ $visitorInitials }}</span>
                    <div>
                        <div class="note-visitor-name">{{ $visitor }}</div>
                        <div class="note-visitor-meta">
                            <span>{{ $conversation?->website?->name ?? 'Unknown website' }}</span>
                            <span aria-hidden="true">&middot;</span>
                            @if ($session->ended_at)
                                <span>Closed {{ optional($session->ended_at)->format('d M, h:i A') }}</span>
                            @else
                                <span>Live chat active</span>
                            @endif
                        </div>
                    </div>
                </div>

                <a class="note-back" href="{{ $backUrl }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 18 9 12l6-6M10 12h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back
                </a>
            </div>

            <form class="note-form" method="POST" action="{{ route('agent.closed.note.update', $session) }}">
                @csrf
                @method('PATCH')

                <div class="note-section-head">
                    <label class="note-section-title" for="note">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Internal Note
                    </label>
                    <div class="note-help">This note is for internal use only. It will not be visible to the visitor.</div>
                </div>

                <textarea
                    id="note"
                    class="note-textarea"
                    name="note"
                    maxlength="5000"
                    placeholder="Write the visitor's query, solution provided, and any important follow-up..."
                    required
                    data-note-textarea
                >{{ old('note', $session->note) }}</textarea>
                <div class="note-count" data-note-count>0/5000</div>

                <div class="note-actions">
                    <button class="btn primary note-save" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 4h12l2 2v14H5zM8 4v6h8V4M8 17h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save Note
                    </button>
                    <a class="btn gray note-cancel" href="{{ $backUrl }}">
                        Cancel
                    </a>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.querySelectorAll('[data-note-textarea]').forEach(textarea => {
            const count = document.querySelector('[data-note-count]');
            const sync = () => {
                if (count) {
                    count.textContent = `${textarea.value.length}/5000`;
                }
            };

            sync();
            textarea.addEventListener('input', sync);
        });
    </script>
@endsection
