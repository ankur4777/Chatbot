@extends('agent.layout', ['title' => 'Profile'])

@section('content')
    @php
        $availability = $agent->availability_status ?? 'offline';
        $availabilityLabel = match ($availability) {
            'online' => 'Online',
            'away' => 'On Break',
            default => 'Offline',
        };
        $average = $ratingStats['average'];
        $ratingCount = $ratingStats['count'];
        $lastSeen = $availability === 'online'
    ? 'Active now'
    : ($lastSeenDisplay ?? 'Never');
    @endphp

    <div class="profile-grid">
        <section class="profile-activity-panel panel">
            <div class="panel-header">
                <strong>Today's Activity</strong>
            </div>
            <div class="panel-body profile-activity-cards">
                <div class="profile-activity-card login">
                    <div class="profile-activity-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="profile-stat-title">Total Online Time</div>
                        <div
                            class="profile-activity-value"
                            data-activity-live-card="online"
                            data-seconds="{{ $activityCalendar['today']['login_seconds'] ?? 0 }}"
                            data-running="{{ ! empty($activityCalendar['today']['online_running']) ? 'true' : 'false' }}"
                        >{{ $activityCalendar['today']['online_time'] ?? '0m' }}</div>
                    </div>
                </div>
                <div class="profile-activity-card login-time">
                    <div class="profile-activity-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 12h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="m11 8 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 5v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="profile-stat-title">Today Login Time</div>
                        <div class="profile-activity-value">
                            {{ $activityCalendar['today']['login_time'] ?? 'N/A' }}
                        </div>
                    </div>
                </div>
                <div class="profile-activity-card break">
                    <div class="profile-activity-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 8h10v5a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4V8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 10h2a2 2 0 0 1 0 4h-2M9 4v2M13 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="profile-stat-title">Total Break Time</div>
                        <div
                            class="profile-activity-value"
                            data-activity-live-card="break"
                            data-seconds="{{ $activityCalendar['today']['break_seconds'] ?? 0 }}"
                            data-running="{{ ! empty($activityCalendar['today']['break_running']) ? 'true' : 'false' }}"
                        >{{ $activityCalendar['today']['break_time'] ?? '0m' }}</div>
                    </div>
                </div>
                <div class="profile-activity-card handled">
                    <div class="profile-activity-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="profile-stat-title">Total Chats Handled</div>
                        <div class="profile-activity-value">{{ $closedTodayCount ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="profile-hero panel">
            <div class="profile-summary">
                <h2>{{ $agent->name }}</h2>
                <div class="profile-role">Support Agent</div>
                <div class="availability-pill {{ $availability }}">
                    <span></span>
                    {{ $availabilityLabel }}
                </div>
            </div>

            <div class="profile-detail-list">
                <div class="profile-detail-row">
                    <span class="profile-detail-label">
                        <span class="profile-detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6-5.4-2.9-5.4 2.9 1-6-4.4-4.3 6.1-.9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>Average Rating</span>
                    </span>
                    <strong>{{ $ratingCount > 0 ? number_format((float) $average, 1) . ' / 5' : 'Not Rated' }}</strong>
                </div>
                <div class="profile-detail-row">
                    <span class="profile-detail-label">
                        <span class="profile-detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M7 3v4M17 3v4M4 8h16M5 5h14v15H5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>Member Since</span>
                    </span>
                    <strong>{{ $agent->created_at?->format('d M Y') ?? 'N/A' }}</strong>
                </div>
                <div class="profile-detail-row">
                    <span class="profile-detail-label">
                        <span class="profile-detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 8v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>Last Seen</span>
                    </span>
                    <strong>{{ $lastSeen }}</strong>
                </div>
            </div>

            <div class="profile-stat-grid">
                <div class="profile-stat">
                    <div class="profile-stat-shell">
                        <span class="profile-stat-icon chat" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <div>
                            <div class="profile-stat-title">Closed Chats</div>
                            <div class="profile-stat-value">{{ $closedSessionsCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-shell">
                        <span class="profile-stat-icon rating" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6-5.4-2.9-5.4 2.9 1-6-4.4-4.3 6.1-.9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <div class="profile-stat-title">Total Ratings</div>
                            <div class="profile-stat-value">{{ $ratingCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="profile-info-panel panel">
            <div class="panel-header">
                <strong>Profile Information</strong>
            </div>
            <div class="panel-body">
                <div class="profile-info-grid">
                    <div class="profile-field">
                        <div class="profile-field-shell">
                            <span class="profile-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="profile-field-title">Full Name</div>
                                <div class="profile-value">{{ $agent->name }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-shell">
                            <span class="profile-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="m5 7 7 6 7-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="profile-field-title">Email Address</div>
                                <div class="profile-value">{{ $agent->email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-shell">
                            <span class="profile-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M8 5 6 7c-1 1-1 3 0 5a17 17 0 0 0 6 6c2 1 4 1 5 0l2-2-4-4-2 2c-2-1-3-2-4-4l2-2-3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="profile-field-title">Phone Number</div>
                                <div class="profile-value">{{ $agent->phone ?: 'Not added' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-shell">
                            <span class="profile-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 21V7l8-4 8 4v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="profile-field-title">Company Name</div>
                                <div class="profile-value">{{ $agent->company?->name ?? 'No company assigned' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-shell">
                            <span class="profile-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="profile-field-title">Assigned Websites</div>
                                @if ($agent->assignedWebsites->isNotEmpty())
                                    <div class="website-chip-list">
                                        @foreach ($agent->assignedWebsites as $website)
                                            <span class="website-chip">{{ $website->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="profile-muted">No websites assigned</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="profile-field">
                        <div class="profile-field-shell">
                            <span class="profile-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6-5.4-2.9-5.4 2.9 1-6-4.4-4.3 6.1-.9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div>
                                <div class="profile-field-title">Average Rating</div>
                                <div class="profile-value">{{ $ratingCount > 0 ? number_format((float) $average, 1) . ' / 5' : 'Not Rated' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="profile-calendar-panel panel">
            <div class="panel-header">
                <div class="profile-calendar-heading">
                    <span class="profile-calendar-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M7 3v4M17 3v4M4 8h16M5 5h14v15H5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 12h3M13 12h3M8 16h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span>
                        <strong>Activity Calendar</strong>
                        <small>View your daily chat activity and performance</small>
                    </span>
                </div>
                <div class="profile-calendar-nav">
                    <span class="profile-muted">{{ $activityCalendar['month'] }}</span>
                    <a class="profile-calendar-nav-button" href="{{ route('agent.profile', ['month' => $activityCalendar['previousMonth']]) }}" aria-label="Previous month">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a class="profile-calendar-nav-button" href="{{ route('agent.profile', ['month' => $activityCalendar['nextMonth']]) }}" aria-label="Next month">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a class="profile-calendar-today-button" href="{{ route('agent.profile') }}">Today</a>
                </div>
            </div>
            <div class="panel-body profile-calendar-layout">
                <div class="profile-calendar" data-activity-calendar>
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                        <div class="profile-calendar-weekday">{{ $weekday }}</div>
                    @endforeach

                    @for ($blank = 0; $blank < $activityCalendar['startWeekday']; $blank++)
                        <div class="profile-calendar-empty"></div>
                    @endfor

                    @foreach ($activityCalendar['days'] as $day)
                        <button
                            class="profile-calendar-day {{ $day['is_today'] ? 'today' : '' }} {{ $day['is_selected'] ? 'selected' : '' }}"
                            type="button"
                            data-date="{{ $day['label'] }}"
                            data-online-time="{{ $day['online_time'] }}"
                            data-break-time="{{ $day['break_time'] }}"
                            data-login-time="{{ $day['login_time'] }}"
                            data-logout-time="{{ $day['logout_time'] }}"
                            data-online-seconds="{{ $day['login_seconds'] }}"
                            data-break-seconds="{{ $day['break_seconds'] }}"
                            data-online-running="{{ $day['online_running'] ? 'true' : 'false' }}"
                            data-break-running="{{ $day['break_running'] ? 'true' : 'false' }}"
                            data-closed-chats="{{ $day['closed_chats_count'] }}"
                            data-missed-chats="{{ $day['missed_chats_count'] }}"
                        >
                            <span>{{ $day['day'] }}</span>
                            <small>{{ $day['online_time'] }}</small>
                        </button>
                    @endforeach
                </div>

                <div class="profile-calendar-detail" data-activity-detail>
                    <div class="profile-calendar-detail-title">
                        <span class="profile-calendar-detail-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 20V9M10 20V5M16 20v-8M4 20h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="m7 12 3-3 3 3 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>Day Summary</span>
                    </div>
                    <div class="profile-calendar-detail-date" data-activity-detail-date>
                        {{ $activityCalendar['selectedDay']['label'] ?? 'Today' }}
                    </div>
                    <div class="profile-summary-row">
                        <span class="profile-summary-label chat">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Total Chats</span>
                        </span>
                        <strong data-activity-detail-closed>{{ $activityCalendar['selectedDay']['closed_chats_count'] ?? 0 }}</strong>
                    </div>
                    <div class="profile-summary-row">
                        <span class="profile-summary-label missed">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 8v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M12 16h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            </svg>
                            <span>Missed Chats</span>
                        </span>
                        <strong data-activity-detail-missed>{{ $activityCalendar['selectedDay']['missed_chats_count'] ?? 0 }}</strong>
                    </div>
                    <div class="profile-summary-row">
                        <span class="profile-summary-label online">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 8v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Total Online Time</span>
                        </span>
                        <strong data-activity-detail-online>{{ $activityCalendar['selectedDay']['online_time'] ?? '0m' }}</strong>
                    </div>
                    <div class="profile-summary-row">
                        <span class="profile-summary-label break">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 8h10v5a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4V8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 10h2a2 2 0 0 1 0 4h-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <span>Total Break Time</span>
                        </span>
                        <strong data-activity-detail-break>{{ $activityCalendar['selectedDay']['break_time'] ?? '0m' }}</strong>
                    </div>
                    <div class="profile-summary-row">
                        <span class="profile-summary-label login">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 12h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="m11 8 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 5v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <span>Login Time</span>
                        </span>
                        <strong data-activity-detail-login>{{ $activityCalendar['selectedDay']['login_time'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="profile-summary-row">
                        <span class="profile-summary-label logout">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M15 12H4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="m8 8-4 4 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 5v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <span>Logout Time</span>
                        </span>
                        <strong data-activity-detail-logout>{{ $activityCalendar['selectedDay']['logout_time'] ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.querySelectorAll('[data-activity-calendar]').forEach(calendar => {
            const detail = document.querySelector('[data-activity-detail]');
            const date = detail?.querySelector('[data-activity-detail-date]');
            const online = detail?.querySelector('[data-activity-detail-online]');
            const breakTime = detail?.querySelector('[data-activity-detail-break]');
            const loginTime = detail?.querySelector('[data-activity-detail-login]');
            const logoutTime = detail?.querySelector('[data-activity-detail-logout]');
            const closedChats = detail?.querySelector('[data-activity-detail-closed]');
            const missedChats = detail?.querySelector('[data-activity-detail-missed]');
            const cards = document.querySelectorAll('[data-activity-live-card]');

            const formatDuration = seconds => {
                const value = Math.max(0, Number(seconds) || 0);
                const hours = Math.floor(value / 3600);
                const minutes = Math.floor((value % 3600) / 60);

                if (value === 0) {
                    return '0m';
                }

                if (value < 60) {
                    return '<1m';
                }

                if (hours < 1) {
                    return `${minutes}m`;
                }

                return minutes > 0 ? `${hours}h ${minutes}m` : `${hours}h`;
            };

            calendar.querySelectorAll('[data-date]').forEach(button => {
                button.addEventListener('click', () => {
                    calendar.querySelectorAll('[data-date]').forEach(day => day.classList.remove('selected'));
                    button.classList.add('selected');

                    if (date) {
                        date.textContent = button.dataset.date || '';
                    }
                    if (online) {
                        online.textContent = formatDuration(button.dataset.onlineSeconds);
                    }
                    if (breakTime) {
                        breakTime.textContent = formatDuration(button.dataset.breakSeconds);
                    }
                    if (loginTime) {
                        loginTime.textContent = button.dataset.loginTime || 'N/A';
                    }
                    if (logoutTime) {
                        logoutTime.textContent = button.dataset.logoutTime || 'N/A';
                    }
                    if (closedChats) {
                        closedChats.textContent = button.dataset.closedChats || '0';
                    }
                    if (missedChats) {
                        missedChats.textContent = button.dataset.missedChats || '0';
                    }
                });
            });

            (calendar.querySelector('.selected') || calendar.querySelector('.today'))?.classList.add('selected');

            setInterval(() => {
                const selectedDay = calendar.querySelector('.profile-calendar-day.selected');

                cards.forEach(card => {
                    if (card.dataset.running !== 'true') {
                        return;
                    }

                    card.dataset.seconds = String((Number(card.dataset.seconds) || 0) + 1);
                    card.textContent = formatDuration(card.dataset.seconds);
                });

                calendar.querySelectorAll('[data-date]').forEach(day => {
                    if (day.dataset.onlineRunning === 'true') {
                        day.dataset.onlineSeconds = String((Number(day.dataset.onlineSeconds) || 0) + 1);
                        day.dataset.onlineTime = formatDuration(day.dataset.onlineSeconds);
                        day.querySelector('small').textContent = day.dataset.onlineTime;
                    }

                    if (day.dataset.breakRunning === 'true') {
                        day.dataset.breakSeconds = String((Number(day.dataset.breakSeconds) || 0) + 1);
                        day.dataset.breakTime = formatDuration(day.dataset.breakSeconds);
                    }
                });

                if (selectedDay && online && breakTime) {
                    online.textContent = formatDuration(selectedDay.dataset.onlineSeconds);
                    breakTime.textContent = formatDuration(selectedDay.dataset.breakSeconds);
                }
            }, 1000);
        });
    </script>
@endsection
