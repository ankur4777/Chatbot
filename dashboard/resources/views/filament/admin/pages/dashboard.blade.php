<x-filament-panels::page>
    <div class="aw-page">
        <section class="aw-header" aria-labelledby="aw-overview-title">
            <div>
                <p class="aw-eyebrow">SUPER ADMIN / OVERVIEW</p>
                <h1 id="aw-overview-title">Welcome back, {{ $adminName }}</h1>
                <p>Manage your companies, websites, and users in one place.</p>
            </div>
            <div class="aw-header-actions">
                <a class="aw-btn aw-btn-primary" href="{{ $links['newCompany'] }}">+ Add company</a>
                <a class="aw-btn aw-btn-light" href="{{ $links['websites'] }}">View websites</a>
            </div>
        </section>

        <section class="aw-stats" aria-label="Platform summary">
            <a class="aw-stat" href="{{ $links['companies'] }}">
                <div class="aw-stat-top"><span>Companies</span><span class="aw-stat-icon" aria-hidden="true">▣</span></div>
                <strong>{{ number_format($companyCount) }}</strong>
                <small>{{ number_format($activeCompanyCount) }} active companies</small>
            </a>
            <a class="aw-stat" href="{{ $links['websites'] }}">
                <div class="aw-stat-top"><span>Websites</span><span class="aw-stat-icon" aria-hidden="true">◎</span></div>
                <strong>{{ number_format($websiteCount) }}</strong>
                <small>{{ number_format($activeWebsiteCount) }} active websites</small>
            </a>
            <a class="aw-stat" href="{{ $links['users'] }}">
                <div class="aw-stat-top"><span>Users</span><span class="aw-stat-icon" aria-hidden="true">♙</span></div>
                <strong>{{ number_format($userCount) }}</strong>
                <small>{{ number_format($ownerCount) }} client owners</small>
            </a>
            <a class="aw-stat" href="{{ $links['users'] }}">
                <div class="aw-stat-top"><span>Live chat agents</span><span class="aw-stat-icon" aria-hidden="true">◷</span></div>
                <strong>{{ number_format($agentCount) }}</strong>
                <small>Registered agent accounts</small>
            </a>
        </section>

        <div class="aw-columns">
            <section class="aw-panel" aria-labelledby="aw-companies-title">
                <header class="aw-panel-heading">
                    <div>
                        <span class="aw-section-label">RECENT ACTIVITY</span>
                        <h2 id="aw-companies-title">Latest companies</h2>
                    </div>
                    <a href="{{ $links['companies'] }}">View all →</a>
                </header>

                @forelse ($recentCompanies as $company)
                    <div class="aw-list-row">
                        <span class="aw-row-icon" aria-hidden="true">{{ strtoupper(substr($company->name ?: 'C', 0, 1)) }}</span>
                        <div class="aw-row-main">
                            <strong>{{ $company->name }}</strong>
                            <small>{{ $company->created_at?->diffForHumans() ?? 'Recently added' }}</small>
                        </div>
                        <span class="aw-pill {{ $company->status ? 'aw-pill-success' : 'aw-pill-muted' }}">{{ $company->status ? 'Active' : 'Inactive' }}</span>
                    </div>
                @empty
                    <div class="aw-empty"><strong>No companies yet</strong><span>New companies will appear here after they are created.</span></div>
                @endforelse

                <p class="aw-footnote">{{ number_format($newCompaniesCount) }} companies added in the past 7 days</p>
            </section>

            <section class="aw-panel" aria-labelledby="aw-websites-title">
                <header class="aw-panel-heading">
                    <div>
                        <span class="aw-section-label">WEBSITE MANAGEMENT</span>
                        <h2 id="aw-websites-title">Recent websites</h2>
                    </div>
                    <a href="{{ $links['websites'] }}">View all →</a>
                </header>

                @forelse ($recentWebsites as $website)
                    <div class="aw-list-row">
                        <span class="aw-row-icon" aria-hidden="true">◎</span>
                        <div class="aw-row-main">
                            <strong>{{ $website->name }}</strong>
                            <small>{{ $website->company?->name ?? 'Unassigned company' }} · {{ $website->domain ?: 'No domain' }}</small>
                        </div>
                        <span class="aw-pill {{ $website->status ? 'aw-pill-success' : 'aw-pill-muted' }}">{{ $website->status ? 'Active' : 'Inactive' }}</span>
                    </div>
                @empty
                    <div class="aw-empty"><strong>No websites yet</strong><span>Registered company websites will appear here.</span></div>
                @endforelse
            </section>
        </div>

        <section class="aw-panel aw-links-panel" aria-labelledby="aw-shortcuts-title">
            <header class="aw-panel-heading">
                <div>
                    <span class="aw-section-label">TOOLS &amp; SETTINGS</span>
                    <h2 id="aw-shortcuts-title">Quick access</h2>
                </div>
            </header>
            <div class="aw-shortcuts">
                <a href="{{ $links['companies'] }}"><span class="aw-shortcut-icon" aria-hidden="true">▣</span><span><strong>Companies</strong><small>Manage organizations</small></span><b aria-hidden="true">↗</b></a>
                <a href="{{ $links['users'] }}"><span class="aw-shortcut-icon" aria-hidden="true">♙</span><span><strong>Users</strong><small>Manage accounts</small></span><b aria-hidden="true">↗</b></a>
                <a href="{{ $links['chatbotSettings'] }}"><span class="aw-shortcut-icon" aria-hidden="true">⚙</span><span><strong>Chatbot settings</strong><small>Website chatbot setup</small></span><b aria-hidden="true">↗</b></a>
                <a href="{{ $links['liveChatSettings'] }}"><span class="aw-shortcut-icon" aria-hidden="true">◷</span><span><strong>Live chat settings</strong><small>Configure live chat</small></span><b aria-hidden="true">↗</b></a>
            </div>
        </section>
        <p class="aw-note">Platform statistics reflect data across all companies. No sample data is displayed.</p>
    </div>
</x-filament-panels::page>
