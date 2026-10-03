@extends('agent.layout', ['title' => 'Closed Chats'])

@section('content')
    <style>
        .closed-page-title {
            margin-bottom: clamp(16px, 1.6vw, 24px);
            margin-top: 0;
        }
        .closed-page-title h2 {
            color: #0f172a;
            font-size: clamp(24px, 2vw, 30px);
            font-weight: 600;
            margin: 0;
        }
        .closed-page-title p {
            color: #64748b;
            font-size: 15px;
            font-weight: 600;
            margin: 6px 0 0;
        }
        .closed-panel-header {
            align-items: center;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: clamp(14px, 1.2vw, 18px) clamp(14px, 1.4vw, 20px);
        }
        .closed-panel-title {
            align-items: center;
            color: #0f172a;
            display: flex;
            font-size: 22px;
            font-weight: 600;
            gap: 12px;
        }
        .closed-panel-title svg {
            color: #FF3B30;
            height: 28px;
            width: 28px;
        }
        .closed-toolbar {
            align-items: center;
            display: flex;
            gap: 10px;
            margin-left: auto;
        }
        .closed-search {
            align-items: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            gap: 10px;
            min-height: 42px;
            padding: 0 12px;
        }
        .closed-search {
            min-width: min(380px, 100%);
        }
        .closed-search svg {
            color: #475569;
            flex: 0 0 auto;
            height: 18px;
            width: 18px;
        }
        .closed-search input {
            border: 0;
            color: #0f172a;
            font: inherit;
            font-size: 15px;
            outline: 0;
            width: 100%;
        }
        .closed-table-wrap {
            overflow: visible;
            padding: 0 20px;
        }
        .closed-table {
            border-collapse: collapse;
            width: 100%;
        }
        .closed-table th {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            color: #475569;
            font-size: 15px;
            font-weight: 600;
            padding: 16px;
            text-align: left;
        }
        .closed-table td {
            border-bottom: 1px solid #e5e7eb;
            color: #0f172a;
            font-size: 16px;
            font-weight: 500;
            padding: 16px;
            vertical-align: middle;
        }
        .closed-visitor {
            align-items: center;
            display: flex;
            gap: 14px;
        }
        .closed-avatar {
            align-items: center;
            border-radius: 999px;
            color: #9f1f19;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 16px;
            font-weight: 600;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .closed-avatar.tone-0 { background: #fff1f0; }
        .closed-avatar.tone-1 { background: #dcfce7; color: #166534; }
        .closed-avatar.tone-2 { background: #ffe4e6; color: #be123c; }
        .closed-avatar.tone-3 { background: #ffe2df; color: #DF2F25; }
        .closed-avatar.tone-4 { background: #fae8ff; color: #86198f; }
        .closed-avatar.tone-5 { background: #ffedd5; color: #9a3412; }
        .closed-avatar.tone-6 { background: #ccfbf1; color: #0f766e; }
        .closed-visitor-name {
            font-size: 16px;
            font-weight: 600;
        }
        .closed-visitor-site,
        .closed-detail-sub {
            color: #64748b;
            font-size: 15px;
            margin-top: 4px;
        }
        .closed-detail-main {
            color: #0f172a;
            font-size: 15px;
            line-height: 1.35;
        }
        .rating-badge {
            align-items: center;
            background: #eef2f7;
            border-radius: 8px;
            color: #475569;
            display: inline-flex;
            font-size: 14px;
            font-weight: 600;
            gap: 7px;
            padding: 8px 12px;
            white-space: nowrap;
        }
        .rating-badge svg {
            height: 16px;
            width: 16px;
        }
        .rating-badge.success { background: #dcfce7; color: #166534; }
        .rating-badge.warning { background: #fef3c7; color: #92400e; }
        .rating-badge.danger { background: #fee2e2; color: #991b1b; }
        .closed-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            white-space: nowrap;
        }
        .closed-action {
            align-items: center;
            border-radius: 8px;
            display: inline-flex;
            font-size: 15px;
            font-weight: 600;
            gap: 8px;
            padding: 11px 16px;
        }
        .closed-action svg {
            height: 17px;
            width: 17px;
        }
        .closed-action.open {
            background: #fff1f0;
            color: #0f172a;
        }
        .closed-action.note {
            background: #ffe4e6;
            color: #ba0000;
        }
        .closed-action.note.added {
            background: #dcfce7;
            color: #166534;
        }
        .closed-pagination {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 20px;
        }
        .closed-pages {
            align-items: center;
            display: flex;
            gap: 8px;
        }
        .closed-page-btn {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #FF3B30;
            display: inline-flex;
            height: 42px;
            justify-content: center;
            min-width: 42px;
        }
        .closed-page-btn.current {
            background: #FF3B30;
            border-color: #FF3B30;
            color: #fff;
            font-weight: 600;
        }
        .closed-page-btn.disabled {
            color: #94a3b8;
            cursor: not-allowed;
            opacity: .65;
        }
        @media (max-width: 1500px) {
            .closed-table th,
            .closed-table td {
                font-size: 14px;
                padding: 13px 12px;
            }
            .closed-avatar {
                height: 42px;
                width: 42px;
            }
            .closed-actions {
                gap: 8px;
            }
            .closed-action {
                font-size: 14px;
                padding: 9px 11px;
            }
            .closed-detail-main,
            .closed-detail-sub,
            .closed-visitor-site {
                font-size: 13px;
            }
        }
        @media (max-width: 1199px) {
            .closed-table,
            .closed-table tbody,
            .closed-table tr,
            .closed-table td {
                display: block;
                width: 100%;
            }
            .closed-table thead {
                display: none;
            }
            .closed-table-wrap {
                padding: 0 14px;
            }
            .closed-table tbody {
                display: grid;
                gap: 12px;
            }
            .closed-table tr {
                border: 1px solid var(--border);
                border-radius: 10px;
                overflow: hidden;
            }
            .closed-table td {
                align-items: flex-start;
                border-bottom: 1px solid #e5e7eb;
                display: grid;
                gap: 10px;
                grid-template-columns: 120px minmax(0, 1fr);
                padding: 12px;
            }
            .closed-table td:last-child {
                border-bottom: 0;
            }
            .closed-table td::before {
                color: #64748b;
                content: attr(data-label);
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .02em;
                text-transform: uppercase;
            }
            .closed-table td[colspan] {
                display: block;
            }
            .closed-table td[colspan]::before {
                content: none;
            }
            .closed-actions {
                flex-wrap: wrap;
                justify-content: flex-start;
                white-space: normal;
            }
        }
        @media (max-width: 1100px) {
            .closed-panel-header {
                align-items: flex-start;
                flex-direction: column;
            }
            .closed-toolbar {
                align-items: stretch;
                margin-left: 0;
                width: 100%;
            }
            .closed-search {
                min-width: 0;
                width: 100%;
            }
        }
        @media (max-width: 700px) {
            .closed-toolbar,
            .closed-pagination {
                flex-direction: column;
                align-items: stretch;
            }
            .closed-pages {
                justify-content: flex-end;
            }
            .closed-page-title h2 { font-size: 26px; }
            .closed-panel-title { font-size: 20px; }
            .closed-table-wrap { padding: 0 12px; }
            .closed-actions {
                justify-content: flex-start;
            }
        }
        @media (max-width: 575px) {
            .closed-panel-header { padding: 14px 12px; }
            .closed-avatar {
                height: 42px;
                width: 42px;
            }
            .closed-action {
                padding-left: 12px;
                padding-right: 12px;
            }
            .closed-pagination { padding: 14px 12px; }
            .closed-table td {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="closed-page-title">
        <h2>Closed Chats</h2>
        <p>View all closed live chats with visitors. (Last 30 days) </p>
    </div>

    <section class="panel">
        <div class="closed-panel-header">
            <div class="closed-panel-title">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Closed Live Chats
            </div>

            <form class="closed-toolbar" method="GET" action="{{ route('agent.closed') }}">
                @foreach (request()->except(['search', 'page']) as $key => $value)
                    @if (is_scalar($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label class="closed-search">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search ?? '' }}"
                        placeholder="Search by visitor, message, website..."
                        data-closed-search-input
                    >
                </label>
            </form>
        </div>

        <div class="panel-body" data-realtime-refresh="closed-list">
            <div class="closed-table-wrap">
                <table class="closed-table">
                    <thead>
                        <tr>
                            <th>Visitor</th>
                            <th>Details</th>
                            <th>Rating</th>
                            <th>Closed At</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            @php
                                $conversation = $session->conversation;
                                $uuid = $conversation?->visitor?->visitor_uuid;
                                $visitorCode = $uuid ? substr($uuid, 0, 8) : 'unknown';
                                $visitor = $conversation?->visitor?->displayName() ?? 'Unknown Visitor';
                                $lastMessage = $conversation?->messages?->first()?->message;
                                $closedBy = $session->ended_by === 'visitor'
                                    ? 'Closed by Visitor'
                                    : 'Closed by Agent';
                                $ratingCount = (int) ($session->conversation_rating_count ?? 0);
                                $averageRating = $ratingCount > 0
                                    ? (float) $session->conversation_rating_average
                                    : null;
                                $hasRating = $averageRating !== null;
                                $rating = $hasRating
                                    ? number_format($averageRating, 1) . ' / 5'
                                    : 'Not Rated';
                                $ratingClass = match (true) {
                                    ! $hasRating => 'empty',
                                    $averageRating < 3 => 'danger',
                                    $averageRating < 4 => 'warning',
                                    default => 'success',
                                };
                                $avatar = strtoupper($conversation?->visitor?->initials() ?: 'UV');
                                $tone = abs(crc32((string) $visitorCode)) % 7;
                                $closedAt = $session->ended_at
                                    ? \App\Support\BrowserTime::format($session->ended_at, 'd M Y, h:i A')
                                    : 'N/A';
                            @endphp

                            <tr>
                                <td data-label="Visitor">
                                    <div class="closed-visitor">
                                        <span class="closed-avatar tone-{{ $tone }}">{{ $avatar }}</span>
                                        <div>
                                            <div class="closed-visitor-name">{{ $visitor }}</div>
                                            <div class="closed-visitor-site">{{ $conversation?->website?->name ?? 'Unknown website' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Details">
                                    <div class="closed-detail-main">{{ $lastMessage ? \Illuminate\Support\Str::limit($lastMessage, 60) : 'No message' }}</div>
                                    <div class="closed-detail-sub">{{ $closedBy }}</div>
                                </td>
                                <td data-label="Rating">
                                    <span class="rating-badge {{ $ratingClass }}">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $rating }}
                                    </span>
                                </td>
                                <td data-label="Closed At">{{ $closedAt }}</td>
                                <td data-label="Actions">
                                    <div class="closed-actions">
                                        <a class="closed-action open" href="{{ route('agent.closed.show', $session) }}">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                                            </svg>
                                            Open
                                        </a>
                                        <a
                                            class="closed-action note {{ filled($session->note) ? 'added' : '' }}"
                                            href="{{ route('agent.closed.note.edit', $session) }}"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M7 3h7l5 5v13H7z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                <path d="M14 3v5h5M10 13h6M10 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            {{ filled($session->note) ? 'Note Added' : 'Add Note' }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="muted">No closed chats found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="closed-pagination">
                <div class="muted">
                    Showing {{ $sessions->firstItem() ?? 0 }} to {{ $sessions->lastItem() ?? 0 }} of {{ $sessions->total() }} chats
                </div>

                <div class="closed-pages">
                    @if ($sessions->onFirstPage())
                        <span class="closed-page-btn disabled" aria-disabled="true">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18" aria-hidden="true">
                                <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    @else
                        <a class="closed-page-btn" href="{{ $sessions->previousPageUrl() }}">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18" aria-hidden="true">
                                <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endif

                    <span class="closed-page-btn current">{{ $sessions->currentPage() }}</span>

                    @if ($sessions->hasMorePages())
                        <a class="closed-page-btn" href="{{ $sessions->nextPageUrl() }}">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @else
                        <span class="closed-page-btn disabled" aria-disabled="true">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.querySelector('[data-closed-search-input]');
            const form = input?.closest('form');
            const results = document.querySelector('[data-realtime-refresh="closed-list"]');

            if (!input || !form || !results) {
                return;
            }

            let timer = null;
            let controller = null;

            const buildUrl = (url = form.action, resetPage = true) => {
                const params = new URLSearchParams(window.location.search);
                const value = input.value.trim();

                if (value === '') {
                    params.delete('search');
                } else {
                    params.set('search', value);
                }

                params.delete('page');

                form.querySelectorAll('input[type="hidden"]').forEach((field) => {
                    if (field.name && field.value && !params.has(field.name)) {
                        params.set(field.name, field.value);
                    }
                });

                const nextUrl = new URL(url, window.location.origin);
                const nextParams = new URLSearchParams(nextUrl.search);

                params.forEach((paramValue, key) => {
                    nextParams.set(key, paramValue);
                });

                if (value === '') {
                    nextParams.delete('search');
                }

                nextUrl.search = nextParams.toString();

                return nextUrl;
            };

            const updateResults = async (url, replaceHistory = true) => {
                controller?.abort();
                controller = new AbortController();

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error('Closed chats search failed.');
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextResults = doc.querySelector('[data-realtime-refresh="closed-list"]');

                if (!nextResults) {
                    throw new Error('Closed chats results were not found.');
                }

                results.innerHTML = nextResults.innerHTML;

                if (replaceHistory) {
                    window.history.replaceState({}, '', url);
                }
            };

            const submitSearch = () => {
                const url = buildUrl(form.action, true);

                updateResults(url).catch((error) => {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                });
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(submitSearch, 300);
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                clearTimeout(timer);
                submitSearch();
            });

            results.addEventListener('click', (event) => {
                const link = event.target.closest('.closed-page-btn[href]');

                if (!link) {
                    return;
                }

                event.preventDefault();
                clearTimeout(timer);

                const url = buildUrl(link.href, false);

                updateResults(url).catch((error) => {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                });
            });
        });
    </script>
@endsection
