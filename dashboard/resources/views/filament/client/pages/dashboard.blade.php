<x-filament-panels::page>
    <div class="cw-page">
        <section class="cw-header" aria-labelledby="cw-dashboard-title">
            <div>
                <div class="cw-eyebrow">CLIENT WORKSPACE / OVERVIEW</div>
                <h1 id="cw-dashboard-title">Welcome back, {{ $clientName }}</h1>
                <p>Keep track of your websites, chatbot conversations and leads from one place.</p>
            </div>
            <div class="cw-header-actions">
                <a class="cw-button cw-button-primary" href="{{ $links['conversations'] }}">View conversations</a>
                <a class="cw-button cw-button-secondary" href="{{ $links['websites'] }}">Manage websites</a>
            </div>
        </section>

        <section class="cw-stats" aria-label="Workspace statistics">
            <a class="cw-stat" href="{{ $links['websites'] }}">
                <div class="cw-stat-top"><span class="cw-stat-label">Connected websites</span><span class="cw-stat-icon" aria-hidden="true">◎</span></div>
                <strong class="cw-stat-value">{{ number_format($websiteCount) }}</strong>
                <span class="cw-stat-meta">Your registered websites</span>
            </a>
            <a class="cw-stat" href="{{ $links['conversations'] }}">
                <div class="cw-stat-top"><span class="cw-stat-label">Conversations</span><span class="cw-stat-icon" aria-hidden="true">▤</span></div>
                <strong class="cw-stat-value">{{ number_format($conversationCount) }}</strong>
                <span class="cw-stat-meta"><strong>+{{ number_format($recentConversationCount) }}</strong> in the past 7 days</span>
            </a>
            <a class="cw-stat" href="{{ $links['leads'] }}">
                <div class="cw-stat-top"><span class="cw-stat-label">Chatbot leads</span><span class="cw-stat-icon" aria-hidden="true">✧</span></div>
                <strong class="cw-stat-value">{{ number_format($leadCount) }}</strong>
                <span class="cw-stat-meta"><strong>+{{ number_format($recentLeadCount) }}</strong> in the past 7 days</span>
            </a>
            @if ($hasLiveChat)
                <a class="cw-stat" href="{{ $links['inbox'] }}">
                    <div class="cw-stat-top"><span class="cw-stat-label">Live inbox</span><span class="cw-stat-icon" aria-hidden="true">•</span></div>
                    <strong class="cw-stat-value">{{ number_format($activeChatCount + $waitingCount) }}</strong>
                    <span class="cw-stat-meta">All waiting and live conversations</span>
                </a>
                <a class="cw-stat" href="{{ $links['activeChats'] }}">
                    <div class="cw-stat-top"><span class="cw-stat-label">Active live chats</span><span class="cw-stat-icon" aria-hidden="true">•</span></div>
                    <strong class="cw-stat-value">{{ number_format($activeChatCount) }}</strong>
                    <span class="cw-stat-meta">Agent-connected conversations</span>
                </a>
                <a class="cw-stat" href="{{ $links['waitingChats'] }}">
                    <div class="cw-stat-top"><span class="cw-stat-label">Waiting chats</span><span class="cw-stat-icon" aria-hidden="true">â—·</span></div>
                    <strong class="cw-stat-value">{{ number_format($waitingCount) }}</strong>
                    <span class="cw-stat-meta">Waiting for an available agent</span>
                </a>
            @else
                <a class="cw-stat" href="{{ $links['visitors'] }}">
                    <div class="cw-stat-top"><span class="cw-stat-label">Visitor activity</span><span class="cw-stat-icon" aria-hidden="true">♙</span></div>
                    <strong class="cw-stat-value">View</strong>
                    <span class="cw-stat-meta">Explore website visitors</span>
                </a>
            @endif
        </section>

        <div class="cw-content-grid">
            <section class="cw-panel" aria-labelledby="cw-recent-conversations">
                <header class="cw-panel-title">
                    <h2 id="cw-recent-conversations">Recent conversations</h2>
                    <a href="{{ $links['conversations'] }}">View all →</a>
                </header>
                @forelse ($recentConversations as $conversation)
                    <div class="cw-activity">
                        <div class="cw-avatar" aria-hidden="true">{{ strtoupper(substr($conversation->visitor?->name ?: 'V', 0, 1)) }}</div>
                        <div class="cw-activity-copy">
                            <strong>{{ $conversation->visitor?->displayName() ?? 'Unknown visitor' }}</strong>
                            <small>{{ $conversation->website?->name ?? 'Website' }} · {{ $conversation->updated_at?->diffForHumans() ?? 'Recently' }}</small>
                        </div>
                        <span class="cw-status">{{ str($conversation->status ?? 'active')->replace('_', ' ')->title() }}</span>
                    </div>
                @empty
                    <div class="cw-empty"><strong>No conversations yet</strong>New conversations will appear when visitors start chatting.</div>
                @endforelse
            </section>

            <div class="cw-side-stack">
                <section class="cw-panel" aria-labelledby="cw-leads">
                    <header class="cw-panel-title">
                        <h2 id="cw-leads">Latest leads</h2>
                        <a href="{{ $links['leads'] }}">View all →</a>
                    </header>
                    @forelse ($recentLeads as $lead)
                        <div class="cw-lead">
                            <div class="cw-lead-copy">
                                <strong>{{ $lead->name ?: ($lead->email ?: 'New lead') }}</strong>
                                <small>{{ $lead->website?->name ?? 'Website' }}</small>
                            </div>
                            <time datetime="{{ $lead->created_at?->toIso8601String() }}">{{ $lead->created_at?->diffForHumans() }}</time>
                        </div>
                    @empty
                        <div class="cw-empty"><strong>No leads yet</strong>New enquiries will appear here.</div>
                    @endforelse
                </section>

                <section class="cw-panel" aria-labelledby="cw-shortcuts">
                    <header class="cw-panel-title"><h2 id="cw-shortcuts">Quick access</h2></header>
                    <a class="cw-shortcut" href="{{ $links['websites'] }}"><span class="cw-shortcut-icon" aria-hidden="true">◎</span><span><strong class="cw-shortcut-title">Websites</strong><small class="cw-shortcut-subtitle">Manage connected websites</small></span><span class="cw-shortcut-arrow" aria-hidden="true">↗</span></a>
                    <a class="cw-shortcut" href="{{ $links['leads'] }}"><span class="cw-shortcut-icon" aria-hidden="true">✧</span><span><strong class="cw-shortcut-title">Chatbot leads</strong><small class="cw-shortcut-subtitle">Review enquiries</small></span><span class="cw-shortcut-arrow" aria-hidden="true">↗</span></a>
                    <a class="cw-shortcut" href="{{ $links['visitors'] }}"><span class="cw-shortcut-icon" aria-hidden="true">♙</span><span><strong class="cw-shortcut-title">Visitors</strong><small class="cw-shortcut-subtitle">See visitor activity</small></span><span class="cw-shortcut-arrow" aria-hidden="true">↗</span></a>
                    @if ($hasLiveChat)
                        <a class="cw-shortcut" href="{{ $links['inbox'] }}"><span class="cw-shortcut-icon" aria-hidden="true">▤</span><span><strong class="cw-shortcut-title">Live inbox</strong><small class="cw-shortcut-subtitle">Manage live chats</small></span><span class="cw-shortcut-arrow" aria-hidden="true">↗</span></a>
                    @endif
                </section>
            </div>
        </div>
        <p class="cw-note">Statistics include only data from your company.</p>
    </div>
</x-filament-panels::page>
