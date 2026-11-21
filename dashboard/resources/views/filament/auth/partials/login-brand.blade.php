@if (request()->routeIs('filament.*.auth.login'))
    @php
        $portal = filament()->getId();

        $portalDetails = match ($portal) {
            'admin' => [
                'label' => 'SUPER ADMIN PORTAL',
                'heading' => 'Run your platform with confidence.',
                'description' => 'An organized home for managing companies, websites, and team access.',
                'items' => ['Companies and websites', 'Team and account management', 'Platform settings'],
            ],
            'client' => [
                'label' => 'CLIENT PORTAL',
                'heading' => 'Your customer conversations, organized.',
                'description' => 'Manage customer engagement, leads, and website chat from one workspace.',
                'items' => ['Chatbot conversations', 'Leads and visitors', 'Live chat management'],
            ],
            default => [
                'label' => 'AGENT PORTAL',
                'heading' => 'Every conversation matters.',
                'description' => 'Stay focused on the people waiting for your help and keep your chats moving.',
                'items' => ['Live conversations', 'Incoming chat requests', 'Customer support workspace'],
            ],
        };
    @endphp
    <aside class="shabdd-login-aside" aria-label="Shabdd Innovations welcome">
        <div class="shabdd-login-aside-inner">
            <div class="shabdd-login-brand">
                <img
                    src="{{ asset('images/shabdd-innovations-footer.png') . '?v=3' }}"
                    alt="Shabdd Innovations"
                    width="180"
                    height="53"
                    decoding="async"
                >
                <span class="shabdd-login-brand-rule" aria-hidden="true"></span>
                <span class="shabdd-login-brand-caption">Workspace</span>
            </div>

            <div class="shabdd-login-story">
                <p class="shabdd-login-kicker">{{ $portalDetails['label'] }}</p>
                <h2>{{ $portalDetails['heading'] }}</h2>
                <p class="shabdd-login-description">{{ $portalDetails['description'] }}</p>

                <ul class="shabdd-login-benefits">
                    @foreach ($portalDetails['items'] as $item)
                        <li>
                            <span class="shabdd-login-check" aria-hidden="true">✓</span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="shabdd-login-aside-bottom">
                <span class="shabdd-login-bottom-dot" aria-hidden="true"></span>
                <span>Powered by Shabdd Innovations</span>
            </div>
        </div>
    </aside>
@endif
