<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Agent Dashboard' }}</title>
    <style>
        :root {
            --bg: #f6f7fb;
            --panel: #ffffff;
            --border: #dde3ea;
            --text: #17202a;
            --muted: #657282;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --danger: #dc2626;
            --success: #15803d;
            --warning: #b45309;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        .shell { display: grid; min-height: 100vh; grid-template-columns: 240px minmax(0, 1fr); }
        .sidebar {
            border-right: 1px solid var(--border);
            background: #0f172a;
            color: #e5e7eb;
            padding: 20px 16px;
        }
        .brand { margin-bottom: 22px; font-size: 18px; font-weight: 700; }
        .nav { display: grid; gap: 6px; }
        .nav a, .nav button {
            align-items: center;
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #cbd5e1;
            cursor: pointer;
            display: flex;
            font: inherit;
            justify-content: space-between;
            padding: 10px 12px;
            text-align: left;
        }
        .nav a.active, .nav a:hover, .nav button:hover { background: #1e293b; color: #fff; }
        .nav-badge {
            align-items: center;
            background: #2563eb;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            justify-content: center;
            line-height: 1;
            min-width: 24px;
            padding: 5px 7px;
        }
        .nav a.active .nav-badge,
        .nav a:hover .nav-badge {
            background: #3b82f6;
        }
        .nav .disabled { color: #64748b; cursor: default; padding: 10px 12px; }
        .availability-box {
            border-top: 1px solid #1e293b;
            margin-top: 12px;
            padding-top: 12px;
        }
        .availability-box label {
            color: #94a3b8;
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .availability-box select {
            background: #111827;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e5e7eb;
            font: inherit;
            padding: 9px 10px;
            width: 100%;
        }
        .availability-dot {
            border-radius: 999px;
            display: inline-block;
            height: 8px;
            margin-right: 6px;
            width: 8px;
        }
        .availability-dot.online { background: #22c55e; }
        .availability-dot.away { background: #f59e0b; }
        .availability-dot.offline { background: #64748b; }
        .content { padding: 24px; }
        .topbar {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        h1 { font-size: 26px; margin: 0; }
        .muted { color: var(--muted); }
        .grid { display: grid; gap: 16px; }
        .cards { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .card, .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .card { padding: 18px; }
        .card .value { font-size: 32px; font-weight: 700; margin-top: 8px; }
        .panel-header {
            align-items: center;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            padding: 14px 16px;
        }
        .panel-header strong .badge {
            margin-left: 8px;
            vertical-align: middle;
        }
        .panel-body { padding: 16px; }
        .list { display: grid; gap: 10px; }
        .item {
            align-items: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 9px;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 12px;
            text-decoration: none;
        }
        a.item { cursor: pointer; }
        a.item:hover {
            background: #f8fafc;
            border-color: #93c5fd;
        }
        a.item:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
        .item-main { min-width: 0; }
        .item-title { color: var(--text); font-weight: 700; }
        .item-sub { color: var(--muted); font-size: 13px; margin-top: 4px; }
        .badge {
            border-radius: 999px;
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
        }
        .badge.waiting { background: #fef3c7; color: var(--warning); }
        .badge.active { background: #dcfce7; color: var(--success); }
        .badge.closed { background: #e5e7eb; color: #374151; }
        .closed-list-actions {
            display: grid;
            gap: 8px;
            justify-items: end;
        }
        .closed-list-badges {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: end;
        }
        .rating-badge {
            border-radius: 999px;
            display: inline-flex;
            font-size: 14px;
            font-weight: 800;
            gap: 5px;
            line-height: 1;
            padding: 7px 11px;
        }
        .rating-badge.success { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
        .rating-badge.warning { background: #fef3c7; border: 1px solid #facc15; color: #92400e; }
        .rating-badge.danger { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
        .rating-badge.empty { background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; }
        .btn {
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            font-weight: 700;
            justify-content: center;
            padding: 9px 12px;
        }
        .btn.primary { background: var(--primary); color: #fff; }
        .btn.primary:hover { background: var(--primary-dark); }
        .btn.danger { background: var(--danger); color: #fff; }
        .btn.gray { background: #e5e7eb; color: #111827; }
        .btn.note-required { background: #fee2e2; color: #991b1b; }
        .btn.note-added { background: #dcfce7; color: #166534; }
        .notice, .errors {
            border-radius: 8px;
            margin-bottom: 16px;
            padding: 12px 14px;
        }
        .notice { background: #dcfce7; color: #166534; }
        .errors { background: #fee2e2; color: #991b1b; }
        .chat-layout { display: grid; gap: 16px; grid-template-columns: 280px minmax(0, 1fr); }
        .conversation-list { max-height: calc(100vh - 170px); overflow: auto; }
        .conversation-section-header { border-top: 1px solid var(--border); }
        .conversation-link { display: block; padding: 12px; border-bottom: 1px solid var(--border); }
        .conversation-link.active{ background: #1b2b51; border-radius: 8px; }
        .conversation-link:hover { background: #587cd1; }
        .conversation-link.active .item-title,
        .conversation-link:hover .item-title { color: #fff; }
        .conversation-link.active .item-sub,
        .conversation-link:hover .item-sub { color: #dcd5ba; }
        .chat-panel { display: grid; grid-template-rows: auto minmax(360px, 1fr) auto; min-height: calc(100vh - 120px); }
        .messages { align-content: start; display: grid; gap: 12px; overflow: auto; padding: 18px; }
        .message { align-items: flex-start; display: flex; }
        .message.pending { opacity: .7; }
        .message.agent { justify-content: flex-end; }
        .message.visitor,
        .message.bot,
        .message.system { justify-content: flex-start; }
        .bubble {
            border: 1px solid var(--border);
            border-radius: 12px;
            line-height: 1.45;
            max-width: 76%;
            padding: 10px 12px;
            width: fit-content;
        }
        .message.agent .bubble {
            background: #125dacf8;
            border-color: #2563EB;
            color: #ffffff;
        }
        .message.agent .bubble-meta { color: #dadada; }
        .message.visitor .bubble { background: #fff; }
        .message.bot .bubble { background: #d6f2df; border-color: #bbf7d0; }
        .message.system .bubble { background: #f3f4f6; }
        .bubble-meta { color: var(--muted); font-size: 12px; margin-bottom: 5px; }
        .bubble-text {
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }
        .status-pill {
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            padding: 4px 8px;
            vertical-align: middle;
        }
        .status-pill.live { background: #dcfce7; color: #166534; }
        .status-pill.closed { background: #fee2e2; color: #991b1b; }
        .status-pill.waiting { background: #fef3c7; color: var(--warning); }
        .status-pill.default { background: #e5e7eb; color: #374151; }
        .message-attachment { margin-top: 8px; }
        .message-attachment img,
        .message-attachment video {
            border: 1px solid var(--border);
            border-radius: 8px;
            display: block;
            max-height: 180px;
            max-width: 240px;
            object-fit: contain;
        }
        .attachment-card {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: inline-flex;
            gap: 8px;
            max-width: 260px;
            padding: 8px 10px;
            word-break: break-word;
        }
        .attachment-icon {
            background: #fee2e2;
            border-radius: 6px;
            color: #991b1b;
            flex-shrink: 0;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 6px;
        }
        .composer { border-top: 1px solid var(--border); padding: 14px; }
        .closed-conversation-message {
            align-items: center;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            display: inline-flex;
            font-size: 17px;
            font-weight: 700;
            padding: 10px 12px;
        }
        .typing-row {
            align-items: center;
            background: #dbeafe;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            color: #1d4ed8;
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 12px;
            padding: 7px 12px;
        }
        .typing-row[hidden] {
            display: none;
        }
        .canned-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }
        textarea {
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
            min-height: 86px;
            padding: 10px;
            resize: vertical;
            width: 100%;
        }
        .composer-actions { display: flex; justify-content: flex-end; margin-top: 10px; }
        .pagination { margin-top: 16px; }
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; }
            .cards, .chat-layout { grid-template-columns: 1fr; }
            .content { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Support Agent</div>
            <nav class="nav">
                <a href="{{ route('agent.dashboard') }}" @class(['active' => request()->routeIs('agent.dashboard')])>Dashboard</a>
                <a href="{{ route('agent.waiting') }}" @class(['active' => request()->routeIs('agent.waiting')])>
                    <span>Waiting Chats</span>
                    <span class="nav-badge" data-realtime-refresh="nav-waiting-count">{{ $agentNavWaitingCount ?? 0 }}</span>
                </a>
                <a href="{{ route('agent.chats') }}" @class(['active' => request()->routeIs('agent.chats*')])>
                    <span>My Chats</span>
                    <span class="nav-badge" data-realtime-refresh="nav-active-count">{{ $agentNavActiveCount ?? 0 }}</span>
                </a>
                <a href="{{ route('agent.closed') }}" @class(['active' => request()->routeIs('agent.closed')])>Closed Chats</a>
                <form class="availability-box" method="POST" action="{{ route('agent.availability.update') }}">
                    @csrf
                    <label for="agent-availability">Availability</label>
                    <select
                        id="agent-availability"
                        name="availability_status"
                        onchange="this.form.submit()"
                    >
                        @foreach (['online' => 'Online', 'away' => 'Away', 'offline' => 'Offline'] as $value => $label)
                            <option value="{{ $value }}" @selected((auth()->user()->availability_status ?? 'offline') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <form method="POST" action="{{ route('agent.logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </nav>
        </aside>

        <main class="content">
            <div class="topbar">
                <div>
                    <h1>{{ $title ?? 'Agent Dashboard' }}</h1>
                    <div class="muted">
                        {{ auth()->user()->company?->name }}
                        @php($availability = auth()->user()->availability_status ?? 'offline')
                        <span class="availability-dot {{ $availability }}"></span>{{ ucfirst($availability) }}
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    <script>
        window.AgentRealtime = {
            enabled: @json(config('broadcasting.default') === 'reverb' && filled(config('broadcasting.connections.reverb.key'))),
            key: @json(config('broadcasting.connections.reverb.key')),
            host: @json(config('broadcasting.connections.reverb.options.host')),
            port: @json((int) config('broadcasting.connections.reverb.options.port')),
            scheme: @json(config('broadcasting.connections.reverb.options.scheme')),
            companyId: @json(auth()->user()?->company_id),
            authEndpoint: @json(url('/broadcasting/auth')),
            csrfToken: @json(csrf_token()),
        };
    </script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.2.4/dist/echo.iife.js"></script>
    <script>
        (() => {
            const config = window.AgentRealtime || {};

            const formatBrowserDateTime = value => {
                if (!value) {
                    return '';
                }

                const date = new Date(value);

                if (Number.isNaN(date.getTime())) {
                    return '';
                }

                return date.toLocaleString([], {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            };

            const localizeMessageTimes = root => {
                (root || document)
                    .querySelectorAll('.message[data-created-at]')
                    .forEach(message => {
                        const meta = message.querySelector('.bubble-meta');
                        const localized = formatBrowserDateTime(message.dataset.createdAt);

                        if (!meta || !localized) {
                            return;
                        }

                        const label = meta.textContent
                            .split('·')[0]
                            .split('Â·')[0]
                            .trim();

                        meta.textContent = `${label} · ${localized}`;
                        meta.title = new Date(message.dataset.createdAt).toLocaleString();
                    });
            };

            const statusClassFor = status => {
                const normalized = String(status || '')
                    .toLowerCase()
                    .replace(/\s+/g, '_');

                if (normalized === 'live_active') {
                    return 'live';
                }

                if (['closed', 'resolved', 'ended'].includes(normalized)) {
                    return 'closed';
                }

                if (normalized === 'waiting_agent' || normalized === 'waiting') {
                    return 'waiting';
                }

                return 'default';
            };

            const formatStatusLabel = status => {
                const normalized = String(status || '')
                    .toLowerCase()
                    .replace(/\s+/g, '_');

                if (normalized === 'live_active') {
                    return 'Live Active';
                }

                if (normalized === 'waiting_agent') {
                    return 'Waiting';
                }

                return String(status || 'closed')
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, character => character.toUpperCase());
            };

            const updateConversationStatusPill = root => {
                (root || document)
                    .querySelectorAll('[data-conversation-status]')
                    .forEach(status => {
                        const label = formatStatusLabel(status.textContent.trim());

                        status.textContent = label;
                        status.className = `status-pill ${statusClassFor(label)}`;
                    });
            };

            localizeMessageTimes(document);
            updateConversationStatusPill(document);

            const refreshTargets = () => {
                const targets = document.querySelectorAll('[data-realtime-refresh]');

                if (!targets.length) {
                    return;
                }

                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(response => response.text())
                    .then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');

                        targets.forEach(target => {
                            const name = target.dataset.realtimeRefresh;
                            const replacement = doc.querySelector(`[data-realtime-refresh="${name}"]`);

                            if (replacement) {
                                target.innerHTML = replacement.innerHTML;
                                localizeMessageTimes(target);
                                updateConversationStatusPill(target);
                            }
                        });
                    })
                    .catch(() => {});
            };

            window.setInterval(refreshTargets, 5000);

            if (!config.enabled || !window.Pusher || !window.Echo) {
                return;
            }

            const EchoCtor = window.Echo.default || window.Echo;

            try {
                const echo = new EchoCtor({
                    broadcaster: 'reverb',
                    key: config.key,
                    cluster: 'mt1',
                    wsHost: config.host,
                    wsPort: config.port,
                    wssPort: config.port,
                    forceTLS: config.scheme === 'https',
                    enabledTransports: config.scheme === 'https' ? ['wss'] : ['ws'],
                    disableStats: true,
                    authEndpoint: config.authEndpoint,
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json',
                        },
                    },
                });

                window.AgentEcho = echo;

                if (config.companyId) {
                    echo.private(`company-live-chat.${config.companyId}`)
                        .listen('.LiveChatRequested', refreshTargets)
                        .listen('.AgentJoinedConversation', refreshTargets)
                        .listen('.LiveChatClosed', refreshTargets);
                }

                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', () => {
                        form.querySelectorAll('button[type="submit"]').forEach(button => {
                            button.disabled = true;
                        });
                    });
                });

                const chatPanel = document.querySelector('[data-conversation-id]');

                if (!chatPanel) {
                    return;
                }

                const conversationId = chatPanel.dataset.conversationId;
                const messages = document.querySelector('[data-message-list]');
                const composer = document.querySelector('[data-message-composer]');
                const visitorTyping = document.querySelector('[data-visitor-typing]');
                let visitorTypingTimeout = null;
                let agentTypingThrottle = null;
                const renderedIds = new Set(
                    Array.from(document.querySelectorAll('[data-message-id]'))
                        .map(element => element.dataset.messageId)
                );

                const nearBottom = () => {
                    if (!messages) {
                        return false;
                    }

                    return messages.scrollHeight - messages.scrollTop - messages.clientHeight < 120;
                };

                const renderAttachment = attachment => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'message-attachment';

                    const url = attachment.view_url || '#';

                    if (attachment.type === 'image') {
                        const link = document.createElement('a');
                        link.href = url;
                        link.target = '_blank';
                        link.rel = 'noopener';

                        const image = document.createElement('img');
                        image.src = url;
                        image.alt = attachment.name || 'Attachment';

                        link.appendChild(image);
                        wrapper.appendChild(link);

                        return wrapper;
                    }

                    if (attachment.type === 'video') {
                        const video = document.createElement('video');
                        video.src = url;
                        video.controls = true;
                        video.preload = 'metadata';

                        wrapper.appendChild(video);

                        return wrapper;
                    }

                    const link = document.createElement('a');
                    link.className = 'attachment-card';
                    link.href = url;
                    link.target = '_blank';
                    link.rel = 'noopener';

                    const icon = document.createElement('span');
                    icon.className = 'attachment-icon';
                    icon.textContent = 'PDF';

                    const name = document.createElement('span');
                    name.textContent = attachment.name || 'Attachment';

                    link.appendChild(icon);
                    link.appendChild(name);
                    wrapper.appendChild(link);

                    return wrapper;
                };

                const appendMessage = event => {
                    if (!messages || !event.message_id || renderedIds.has(String(event.message_id))) {
                        return;
                    }

                    const shouldScroll = nearBottom();
                    const sender = ['visitor', 'user'].includes(event.sender_type)
                        ? 'visitor'
                        : ['bot', 'assistant', 'ai'].includes(event.sender_type)
                            ? 'bot'
                            : event.sender_type === 'agent'
                                ? 'agent'
                                : 'system';

                    const label = sender === 'visitor'
                        ? 'Visitor'
                        : sender === 'bot'
                            ? 'AI Assistant'
                            : sender === 'agent'
                                ? 'Agent'
                                : 'System';

                    const row = document.createElement('div');
                    row.className = `message ${sender}`;
                    row.dataset.messageId = event.message_id;

                    const bubble = document.createElement('div');
                    bubble.className = 'bubble';

                    const meta = document.createElement('div');
                    meta.className = 'bubble-meta';
                    meta.textContent = `${label} · ${formatBrowserDateTime(event.created_at)}`;
                    meta.title = new Date(event.created_at).toLocaleString();

                    const text = document.createElement('div');
                    text.className = 'bubble-text';
                    text.textContent = event.message || '';

                    bubble.appendChild(meta);

                    if (event.message) {
                        bubble.appendChild(text);
                    }

                    if (event.attachment) {
                        bubble.appendChild(renderAttachment(event.attachment));
                    }

                    row.appendChild(bubble);
                    messages.appendChild(row);
                    renderedIds.add(String(event.message_id));

                    if (shouldScroll) {
                        messages.scrollTop = messages.scrollHeight;
                    }

                    if (sender === 'visitor') {
                        try {
                            liveChannel.whisper('messages_read', {
                                conversation_id: conversationId,
                                message_ids: [event.message_id],
                            });
                        } catch (error) {
                            console.warn('Unable to send read receipt.', error);
                        }
                    }
                };

                const hideVisitorTyping = () => {
                    if (!visitorTyping) {
                        return;
                    }

                    visitorTyping.hidden = true;
                    clearTimeout(visitorTypingTimeout);
                    visitorTypingTimeout = null;
                };

                const showVisitorTyping = event => {
                    if (!visitorTyping) {
                        return;
                    }

                    if (
                        event?.conversation_id &&
                        String(event.conversation_id) !== String(conversationId)
                    ) {
                        return;
                    }

                    if (event?.typing === false) {
                        hideVisitorTyping();
                        return;
                    }

                    visitorTyping.hidden = false;

                    clearTimeout(visitorTypingTimeout);
                    visitorTypingTimeout = setTimeout(() => {
                        visitorTyping.hidden = true;
                    }, 3000);
                };

                const closeConversation = event => {
                    if (String(event.conversation_id) !== String(conversationId)) {
                        return;
                    }

                    const status = document.querySelector('[data-conversation-status]');
                    const conversationEnded = event.conversation_ended
                        || ['closed', 'resolved', 'ended'].includes(event.status);

                    if (status) {
                        status.textContent = formatStatusLabel(event.status || 'closed');
                        status.className = `status-pill ${statusClassFor(event.status || 'closed')}`;
                    }

                    hideVisitorTyping();

                    if (composer) {
                        composer.innerHTML = conversationEnded
                            ? '<div class="closed-conversation-message">This conversation is closed.</div>'
                            : '<div class="muted">Live support has ended. The visitor can continue with AI.</div>';
                    }
                };

                const liveChannel = echo.private(`live-chat.${conversationId}`)
                    .listen('.LiveChatMessageSent', appendMessage)
                    .listen('.LiveChatClosed', closeConversation)
                    .listenForWhisper('visitor_typing', showVisitorTyping);

                const openedVisitorMessageIds = Array.from(
                    document.querySelectorAll('.message.visitor[data-message-id]')
                ).map(message => message.dataset.messageId).filter(Boolean);

                if (openedVisitorMessageIds.length > 0) {
                    try {
                        liveChannel.whisper('messages_read', {
                            conversation_id: conversationId,
                            message_ids: openedVisitorMessageIds,
                        });
                    } catch (error) {
                        console.warn('Unable to send read receipt.', error);
                    }
                }

                const textarea = document.querySelector('[data-agent-composer-textarea]');

                if (textarea) {
                    textarea.addEventListener('keydown', event => {
                        if (event.key !== 'Enter' || event.shiftKey) {
                            return;
                        }

                        event.preventDefault();

                        if (!textarea.value.trim()) {
                            textarea.reportValidity();
                            return;
                        }

                        textarea.form?.requestSubmit();
                    });

                    textarea.addEventListener('input', () => {
                        if (agentTypingThrottle) {
                            return;
                        }

                        agentTypingThrottle = setTimeout(() => {
                            agentTypingThrottle = null;
                        }, 1200);

                        try {
                            liveChannel.whisper('agent_typing', {
                                conversation_id: conversationId,
                            });
                        } catch (error) {
                            console.warn('Unable to send typing indicator.', error);
                        }
                    });
                }

                document.querySelectorAll('[data-canned-reply]').forEach(button => {
                    button.addEventListener('click', () => {
                        if (!textarea) {
                            return;
                        }

                        textarea.value = button.dataset.cannedReply || '';
                        textarea.focus();
                    });
                });

            } catch (error) {
                console.warn('Realtime unavailable; HTTP chat remains active.', error);
            }
        })();
    </script>
</body>
</html>
