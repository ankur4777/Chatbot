@extends('agent.layout', ['title' => 'My Chats'])

@section('content')
    <style>
        .my-chats-page { display: grid; gap: 18px; }
        .my-chats-title h2 {
            color: #0f172a;
            font-size: clamp(24px, 2vw, 32px);
            font-weight: 650;
            line-height: 1.1;
            margin: 0;
        }
        .my-chats-title p {
            color: #475569;
            font-size: 15px;
            font-weight: 500;
            margin: 8px 0 0;
        }
        .my-chats-panel { overflow: hidden; }
        .my-chats-panel-header {
            align-items: center;
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(260px, 1fr) minmax(260px, 420px) minmax(150px, 180px);
            padding: 18px 20px;
        }
        .my-chats-panel-title {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
        }
        .my-chats-panel-icon {
            align-items: center;
            background: #fff1f0;
            border-radius: 8px;
            color: #FF3B30;
            display: inline-flex;
            flex: 0 0 auto;
            height: 36px;
            justify-content: center;
            width: 36px;
        }
        .my-chats-panel-icon svg { height: 20px; width: 20px; }
        .my-chats-panel-title strong {
            color: #0f172a;
            font-size: 18px;
            font-weight: 650;
            line-height: 1.2;
        }
        .my-chats-count {
            align-items: center;
            background: #FF3B30;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            height: 28px;
            justify-content: center;
            min-width: 28px;
            padding: 0 9px;
        }
        .my-chats-search,
        .my-chats-sort {
            align-items: center;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            min-height: 46px;
            padding: 0 14px;
        }
        .my-chats-search svg,
        .my-chats-sort svg {
            color: #334155;
            flex: 0 0 auto;
            height: 18px;
            width: 18px;
        }
        .my-chats-search input,
        .my-chats-sort select {
            border: 0;
            color: #334155;
            font: inherit;
            font-size: 14px;
            font-weight: 500;
            min-width: 0;
            outline: 0;
            width: 100%;
        }
        .my-chats-sort select {
            background: transparent;
            cursor: pointer;
            font-weight: 600;
        }
        .my-chats-body {
            border-top: 1px solid var(--border);
            padding: 18px 20px 20px;
        }
        .my-chat-list { display: grid; gap: 12px; }
        .my-chat-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(0, 1fr) auto auto;
            min-width: 0;
            padding: 18px;
        }
        .my-chat-card:hover {
            border-color: #ffc4bf;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }
        .my-chat-card[hidden],
        .my-chats-empty[hidden],
        .pagination[hidden] {
            display: none;
        }
        .my-chat-main {
            align-items: center;
            display: flex;
            gap: 16px;
            min-width: 0;
        }
        .my-chat-avatar {
            align-items: center;
            background: #fff1f0;
            border-radius: 999px;
            color: #5b21b6;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 22px;
            font-weight: 700;
            height: 64px;
            justify-content: center;
            width: 64px;
        }
        .my-chat-copy { min-width: 0; }
        .my-chat-name-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .my-chat-name {
            color: #0f172a;
            font-size: 18px;
            font-weight: 650;
            line-height: 1.2;
        }
        .my-chat-status {
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 650;
            line-height: 1;
            padding: 6px 9px;
        }
        .my-chat-status.active { background: #dcfce7; color: #15803d; }
        .my-chat-status.on_hold { background: #fef3c7; color: #b45309; }
        .my-chat-status.awaiting_visitor { background: #ffe2df; color: #DF2F25; }
        .my-chat-site,
        .my-chat-preview,
        .my-chat-date {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
        }
        .my-chat-site { display: block; margin-top: 6px; }
        .my-chat-preview {
            display: block;
            margin-top: 7px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .my-chat-live {
            align-items: center;
            background: #dcfce7;
            border-radius: 999px;
            color: #15803d;
            display: inline-flex;
            font-size: 13px;
            font-weight: 650;
            gap: 7px;
            padding: 8px 12px;
            white-space: nowrap;
        }
        .my-chat-live::before {
            background: currentColor;
            border-radius: 999px;
            content: "";
            height: 8px;
            width: 8px;
        }
        .my-chat-side {
            display: grid;
            gap: 8px;
            justify-items: end;
        }
        .my-chat-open {
            align-items: center;
            background: #FF3B30;
            border-radius: 8px;
            color: #fff;
            display: inline-flex;
            font-size: 15px;
            font-weight: 650;
            gap: 8px;
            justify-content: center;
            min-height: 46px;
            min-width: 116px;
            padding: 0 18px;
        }
        .my-chat-open svg { height: 18px; width: 18px; }
        .my-chats-empty {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 0;
        }
        @media (max-width: 1200px) {
            .my-chats-panel-header { grid-template-columns: 1fr; }
            .my-chat-card { grid-template-columns: minmax(0, 1fr) auto; }
            .my-chat-side {
                grid-column: 1 / -1;
                grid-template-columns: auto auto;
                justify-content: start;
            }
        }
        @media (max-width: 700px) {
            .my-chats-panel-header,
            .my-chats-body {
                padding-left: 14px;
                padding-right: 14px;
            }
            .my-chat-card {
                align-items: flex-start;
                grid-template-columns: 1fr;
            }
            .my-chat-main {
                align-items: flex-start;
                display: grid;
                gap: 12px;
            }
            .my-chat-avatar {
                height: 52px;
                width: 52px;
            }
            .my-chat-side {
                grid-template-columns: 1fr;
                justify-items: start;
            }
            .my-chat-open { width: 100%; }
        }
    </style>

    <div class="my-chats-page">
        <div class="my-chats-title">
            <h2>My Chats</h2>
            <p>Manage your active conversations assigned to you.</p>
        </div>

        <section class="panel my-chats-panel">
            <div class="my-chats-panel-header">
                <div class="my-chats-panel-title">
                    <span class="my-chats-panel-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M21 14a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <strong>Active Conversations Assigned to You</strong>
                    <span class="my-chats-count">{{ $conversations->total() }}</span>
                </div>

                <label class="my-chats-search">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input type="search" placeholder="Search by visitor, message, or website..." data-my-chat-search>
                </label>

                <label class="my-chats-sort">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 4v16m0 0-3-3m3 3 3-3M17 20V4m0 0-3 3m3-3 3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <select data-my-chat-sort aria-label="Sort conversations">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </label>
            </div>

            <div class="my-chats-body" data-realtime-refresh="active-list">
                <div class="my-chat-list" data-my-chat-list>
                    @forelse ($conversations as $conversation)
                        @php
                            $visitorUuid = $conversation->visitor?->visitor_uuid;
                            $visitor = $conversation->visitor?->displayName() ?? 'Unknown Visitor';
                            $avatar = strtoupper($conversation->visitor?->initials() ?: 'UV');
                            $lastMessage = $conversation->messages()->latest('id')->value('message');
                            $agentChatStatus = $conversation->activeLiveChatSession?->agent_chat_status
                                ?: \App\Models\LiveChatSession::AGENT_CHAT_STATUS_ACTIVE;
                            $agentChatStatusLabel = \App\Models\LiveChatSession::agentChatStatusLabels()[$agentChatStatus] ?? 'Active';
                            $updatedAt = $conversation->updated_at;
                            $searchText = \Illuminate\Support\Str::lower(implode(' ', array_filter([
                                $visitor,
                                $visitorUuid,
                                $conversation->website?->name,
                                $lastMessage,
                                $conversation->visitor?->phone,
                                $conversation->visitor?->email,
                            ])));
                        @endphp

                        <article
                            class="my-chat-card"
                            data-my-chat-card
                            data-search-text="{{ $searchText }}"
                            data-updated-at="{{ optional($updatedAt)->timestamp ?? 0 }}"
                            data-agent-live-chat-link
                            data-conversation-id="{{ $conversation->id }}"
                            data-visitor-label="{{ $visitor }}"
                        >
                            <a class="my-chat-main" href="{{ route('agent.chats.show', $conversation) }}">
                                <span class="my-chat-avatar">{{ $avatar }}</span>
                                <span class="my-chat-copy">
                                    <span class="my-chat-name-row">
                                        <span class="my-chat-name">{{ $visitor }}</span>
                                        <span
                                            class="my-chat-status {{ $agentChatStatus }}"
                                            data-agent-chat-status-badge="{{ $conversation->id }}"
                                        >
                                            {{ $agentChatStatusLabel }}
                                        </span>
                                    </span>
                                    <span class="my-chat-site">{{ $conversation->website?->name ?? 'Unknown website' }}</span>
                                    <span class="my-chat-preview">{{ $lastMessage ? \Illuminate\Support\Str::limit($lastMessage, 100) : 'No messages yet.' }}</span>
                                </span>
                            </a>

                            <div class="my-chat-side">
                                <span class="my-chat-live">Live Active</span>
                                <span class="my-chat-date">{{ $updatedAt ? \App\Support\BrowserTime::format($updatedAt, 'd M Y, h:i A') : 'N/A' }}</span>
                            </div>

                            <a class="my-chat-open" href="{{ route('agent.chats.show', $conversation) }}">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                                Open
                            </a>
                        </article>
                    @empty
                        <div class="my-chats-empty">No active chats assigned to you.</div>
                    @endforelse
                </div>

                <div class="pagination">
                    {{ $conversations->links() }}
                </div>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const search = document.querySelector('[data-my-chat-search]');
            const sort = document.querySelector('[data-my-chat-sort]');
            const body = document.querySelector('[data-realtime-refresh="active-list"]');

            if (!body) {
                return;
            }

            let searchTimer;
            const debounce = callback => {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(callback, 300);
            };

            const applyView = () => {
                const list = body.querySelector('[data-my-chat-list]');
                const pagination = body.querySelector('.pagination');

                if (!list) {
                    return;
                }

                const cards = Array.from(list.querySelectorAll('[data-my-chat-card]'));
                const term = (search?.value || '').trim().toLowerCase();
                const direction = sort?.value || 'newest';
                let visibleCount = 0;

                cards
                    .sort((a, b) => {
                        const first = Number(a.dataset.updatedAt || 0);
                        const second = Number(b.dataset.updatedAt || 0);

                        return direction === 'oldest' ? first - second : second - first;
                    })
                    .forEach(card => {
                        const haystack = (card.dataset.searchText || card.textContent || '').toLowerCase();
                        const isHidden = term !== '' && !haystack.includes(term);

                        card.hidden = isHidden;

                        if (!isHidden) {
                            visibleCount += 1;
                        }

                        list.appendChild(card);
                    });

                list.querySelector('[data-my-chat-filter-empty]')?.remove();

                if (cards.length > 0 && visibleCount === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'my-chats-empty';
                    empty.dataset.myChatFilterEmpty = 'true';
                    empty.textContent = 'No conversations found.';
                    list.appendChild(empty);
                }

                if (pagination) {
                    pagination.hidden = term !== '';
                }
            };

            search?.addEventListener('keydown', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
            search?.addEventListener('input', () => debounce(applyView));
            sort?.addEventListener('change', applyView);
            body.addEventListener('agent:realtime-refreshed', applyView);
            applyView();
        })();
    </script>
@endsection
