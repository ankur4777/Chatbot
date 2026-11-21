<x-filament-panels::page>
    <div class="client-overview">
        <section class="client-hero" aria-labelledby="client-overview-title">
            <div>
                <p class="client-eyebrow">CLIENT WORKSPACE · OVERVIEW</p>
                <h1 id="client-overview-title">Welcome back, {{ $clientName }}<span class="client-punct">.</span></h1>
                <p>See your customer conversations, leads and chatbot performance in one place.</p>
                <div class="client-hero-meta"><span class="client-dot"></span>{{ $companyName }} <span>· Last 7 days highlighted</span></div>
            </div>
            <div class="client-hero-actions">
                <a class="client-button client-button-primary" href="{{ $links['conversations'] }}">View conversations ↗</a>
                <a class="client-button client-button-secondary" href="{{ $links['websites'] }}">Manage websites →</a>
            </div>
        </section>

        <div class="client-section-title">
            <div><small>PERFORMANCE</small><h2>Business at a glance</h2></div>
            <span>Your company's activity</span>
        </div>
        <section class="client-stats" aria-label="Business metrics">
            <a href="{{ $links['websites'] }}" class="client-stat"><span>Connected websites <b>◎</b></span><strong>{{ number_format($websiteCount) }}</strong><small>Websites in your workspace</small></a>
            <a href="{{ $links['conversations'] }}" class="client-stat"><span>All conversations <b>▤</b></span><strong>{{ number_format($conversationCount) }}</strong><small><em>+{{ number_format($recentConversationCount) }}</em> in the past 7 days</small></a>
            <a href="{{ $links['leads'] }}" class="client-stat"><span>Chatbot leads <b>◇</b></span><strong>{{ number_format($leadCount) }}</strong><small><em>+{{ number_format($recentLeadCount) }}</em> in the past 7 days</small></a>
            @if ($hasLiveChat)
                <a href="{{ $links['inbox'] }}" class="client-stat"><span>Waiting live chats <b>◷</b></span><strong>{{ number_format($waitingCount) }}</strong><small>{{ number_format($activeChatCount) }} currently active conversations</small></a>
            @else
                <a href="{{ $links['visitors'] }}" class="client-stat"><span>Visitor management <b>♙</b></span><strong>Explore</strong><small>View your website visitors →</small></a>
            @endif
        </section>

        <div class="client-dashboard-grid">
            <section class="client-panel" aria-labelledby="client-conversations-title">
                <header class="client-panel-heading"><div><small>ACTIVITY FEED</small><h2 id="client-conversations-title">Recent conversations</h2></div><a href="{{ $links['conversations'] }}">View all ↗</a></header>
                @forelse ($recentConversations as $conversation)
                    <div class="client-activity-row">
                        <span class="client-avatar" aria-hidden="true">{{ strtoupper(substr($conversation->visitor?->name ?: 'V', 0, 1)) }}</span>
                        <div class="client-activity-main">
                            <strong>{{ $conversation->visitor?->displayName() ?? 'Unknown visitor' }}</strong>
                            <small>{{ $conversation->website?->name ?? 'Website' }} · {{ $conversation->updated_at?->diffForHumans() ?? 'Recently' }}</small>
                        </div>
                        <span class="client-status-tag">{{ str($conversation->status ?? 'active')->replace('_', ' ')->title() }}</span>
                    </div>
                @empty
                    <div class="client-empty"><strong>No conversations yet</strong><span>Visitor activity will appear here when chats begin.</span></div>
                @endforelse
            </section>

            <div class="client-side-column">
                <section class="client-panel" aria-labelledby="client-shortcuts-title">
                    <header class="client-panel-heading"><div><small>WORKSPACE</small><h2 id="client-shortcuts-title">Quick access</h2></div></header>
                    <a class="client-shortcut" href="{{ $links['websites'] }}"><span>◎</span><div><strong>Websites</strong><small>Manage chatbot connections</small></div><b>↗</b></a>
                    <a class="client-shortcut" href="{{ $links['leads'] }}"><span>◇</span><div><strong>Chatbot leads</strong><small>Follow up with new enquiries</small></div><b>↗</b></a>
                    <a class="client-shortcut" href="{{ $links['visitors'] }}"><span>♙</span><div><strong>Visitors</strong><small>Explore visitor profiles</small></div><b>↗</b></a>
                    @if ($hasLiveChat)
                        <a class="client-shortcut" href="{{ $links['inbox'] }}"><span>▤</span><div><strong>Live inbox</strong><small>Manage incoming requests</small></div><b>↗</b></a>
                    @endif
                </section>
                <section class="client-panel" aria-labelledby="client-leads-title">
                    <header class="client-panel-heading"><div><small>NEW ENQUIRIES</small><h2 id="client-leads-title">Latest leads</h2></div><a href="{{ $links['leads'] }}">View all ↗</a></header>
                    @forelse ($recentLeads as $lead)
                        <div class="client-lead-row"><div><strong>{{ $lead->name ?: ($lead->email ?: 'New lead') }}</strong><small>{{ $lead->website?->name ?? 'Website' }}</small></div><time datetime="{{ $lead->created_at?->toIso8601String() }}">{{ $lead->created_at?->diffForHumans() }}</time></div>
                    @empty
                        <p class="client-empty-small">Your latest chatbot leads will appear here.</p>
                    @endforelse
                </section>
            </div>
        </div>
        <p class="client-footer-note">Metrics reflect records belonging to your company only.</p>
    </div>
</x-filament-panels::page>
