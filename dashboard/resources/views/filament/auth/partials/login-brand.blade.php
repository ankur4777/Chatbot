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

                <div class="shabdd-login-showcase">
                    <ul class="shabdd-login-benefits">
                    @foreach ($portalDetails['items'] as $item)
                        <li>
                            <span class="shabdd-login-check" aria-hidden="true">✓</span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="shabdd-login-mascot" aria-hidden="true">
                    <div class="shabdd-login-mascot-halo"></div>
                    <div class="shabdd-login-mascot-message">
                        <span class="shabdd-login-mascot-dot"></span>
                        Hello there!
                    </div>
                    <svg class="shabdd-login-robot" viewBox="0 0 220 236" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <defs>
                            <linearGradient id="shabdd-robot-shell" x1="32" y1="30" x2="180" y2="208" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFFFFF"/>
                                <stop offset="1" stop-color="#B8C8E3"/>
                            </linearGradient>
                            <linearGradient id="shabdd-robot-screen" x1="52" y1="62" x2="165" y2="144" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#242E4A"/>
                                <stop offset="1" stop-color="#101728"/>
                            </linearGradient>
                            <linearGradient id="shabdd-robot-red" x1="70" y1="160" x2="150" y2="213" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FF7F85"/>
                                <stop offset="1" stop-color="#E94250"/>
                            </linearGradient>
                        </defs>
                        <ellipse cx="110" cy="223" rx="76" ry="10" fill="#080C19" opacity=".25"/>
                        <path d="M110 42V23" stroke="#E6EDF8" stroke-width="8" stroke-linecap="round"/>
                        <circle class="shabdd-robot-antenna" cx="110" cy="19" r="10" fill="#FF626C" stroke="#FFD0D5" stroke-width="3"/>
                        <rect x="13" y="88" width="28" height="49" rx="13" fill="#AABAD3"/>
                        <rect x="179" y="88" width="28" height="49" rx="13" fill="#AABAD3"/>
                        <rect x="25" y="46" width="170" height="114" rx="39" fill="url(#shabdd-robot-shell)" stroke="#F4F8FF" stroke-width="4"/>
                        <rect x="45" y="65" width="130" height="77" rx="28" fill="url(#shabdd-robot-screen)" stroke="#647591" stroke-width="2"/>
                        <circle cx="52" cy="105" r="5" fill="#FF717C" opacity=".8"/>
                        <circle cx="168" cy="105" r="5" fill="#FF717C" opacity=".8"/>
                        <g class="shabdd-robot-eyes">
                            <rect x="73" y="88" width="22" height="27" rx="11" fill="#8AF3E8"/>
                            <rect x="125" y="88" width="22" height="27" rx="11" fill="#8AF3E8"/>
                        </g>
                        <path d="M100 123Q110 133 120 123" stroke="#8AF3E8" stroke-width="4" stroke-linecap="round"/>
                        <rect x="70" y="151" width="80" height="65" rx="24" fill="url(#shabdd-robot-shell)" stroke="#F4F8FF" stroke-width="4"/>
                        <rect x="89" y="169" width="43" height="28" rx="11" fill="url(#shabdd-robot-red)"/>
                        <path d="M98 183H123" stroke="#FFF3F4" stroke-width="3" stroke-linecap="round"/>
                        <path d="M110.5 172.5V193.5" stroke="#FFF3F4" stroke-width="3" stroke-linecap="round"/>
                        <g class="shabdd-robot-arm-left">
                            <path d="M67 172L51 187L37 171" stroke="#C8D6EA" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="35" cy="168" r="12" fill="#F8FAFF"/>
                        </g>
                        <g class="shabdd-robot-arm-right">
                            <path d="M153 172L173 155L182 137" stroke="#C8D6EA" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="183" cy="132" r="13" fill="#F8FAFF"/>
                        </g>
                        <rect x="79" y="205" width="23" height="16" rx="8" fill="#9DAFCB"/>
                        <rect x="118" y="205" width="23" height="16" rx="8" fill="#9DAFCB"/>
                    </svg>
                    <span class="shabdd-login-mascot-caption">Your friendly AI assistant</span>
                </div>
            </div>

            <div class="shabdd-login-aside-bottom">
                <span class="shabdd-login-bottom-dot" aria-hidden="true"></span>
                <span>Powered by Shabdd Innovations</span>
            </div>
        </div>
    </aside>
@endif
