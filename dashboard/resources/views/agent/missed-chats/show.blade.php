@extends('agent.layout', ['title' => 'Missed Chat'])

@section('content')
    @php
        $rawStatus = old('followup_status', $lead->followup_status ?? 'follow_up_required');
        $status = in_array($rawStatus, ['pending', 'assigned'], true) ? 'follow_up_required' : $rawStatus;
        $maxDateTime = '2038-01-19T03:14';
        $statusLabel = $statuses[$status] ?? 'Follow-up Required';
        $assignedAt = $lead->assigned_at ? \App\Support\BrowserTime::format($lead->assigned_at, 'd M Y, h:i A') : 'N/A';
        $lastContacted = $lead->last_contacted_at ? \App\Support\BrowserTime::format($lead->last_contacted_at, 'd M Y, h:i A') : 'Not contacted';
        $nextFollowup = $lead->next_followup_at ? \App\Support\BrowserTime::format($lead->next_followup_at, 'd M Y, h:i A') : 'Not scheduled';
    @endphp

    <style>
        .missed-detail-page { display: grid; gap: 16px; padding-top: 56px; }
        .missed-detail-top {
            align-items: center;
            display: grid;
            gap: 14px;
            grid-template-columns: auto minmax(0, 1fr) minmax(220px, 280px);
        }
        .missed-back {
            align-items: center;
            background: #eaf3ff;
            border-radius: 8px;
            color: #2563eb;
            display: inline-flex;
            font-weight: 600;
            gap: 8px;
            padding: 9px 13px;
        }
        .missed-back svg { height: 17px; width: 17px; }
        .missed-title h2 {
            color: #0f172a;
            font-size: 26px;
            font-weight: 600;
            margin: 0;
        }
        .missed-title p {
            color: #64748b;
            font-weight: 600;
            margin: 5px 0 0;
        }
        .missed-top-field {
            border-left: 1px solid var(--border);
            display: grid;
            gap: 6px;
            padding-left: 20px;
        }
        .missed-top-field label {
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
        }
        .missed-select-shell {
            position: relative;
        }
        .missed-select-shell .status-dot {
            left: 12px;
            pointer-events: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
        }
        .missed-select-shell .status-dot {
            background: #10b981;
            border-radius: 999px;
            height: 10px;
            width: 10px;
        }
        .missed-select-shell .status-dot.contacted { background: #10b981; }
        .missed-select-shell .status-dot.follow_up_required { background: #f59e0b; }
        .missed-select-shell .status-dot.resolved { background: #2563eb; }
        .missed-select-shell .status-dot.unable_to_reach { background: #ef4444; }
        .missed-top-field select {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #0f172a;
            font: inherit;
            font-size: 14px;
            min-height: 42px;
            font-weight: 600;
            padding: 9px 34px 9px 36px;
            width: 100%;
        }
        .missed-summary-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .missed-summary-card {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            gap: 12px;
            min-width: 0;
            padding: 14px;
        }
        .missed-summary-card.assigned { background: #eff6ff; border-color: #bfdbfe; }
        .missed-summary-card.contacted { background: #f0fdf4; border-color: #bbf7d0; }
        .missed-summary-card.next { background: #fffbeb; border-color: #fde68a; }
        .missed-summary-card.status { background: #faf5ff; border-color: #e9d5ff; }
        .missed-summary-icon {
            align-items: center;
            border-radius: 10px;
            display: inline-flex;
            flex: 0 0 auto;
            height: 52px;
            justify-content: center;
            width: 52px;
        }
        .missed-summary-icon svg { height: 26px; width: 26px; }
        .assigned .missed-summary-icon { background: #dbeafe; color: #2563eb; }
        .contacted .missed-summary-icon { background: #dcfce7; color: #16a34a; }
        .next .missed-summary-icon { background: #fef3c7; color: #f59e0b; }
        .status .missed-summary-icon { background: #f3e8ff; color: #a855f7; }
        .missed-summary-title {
            color: #475569;
            font-size: 14px;
            font-weight: 600;
        }
        .missed-summary-value {
            color: #0f172a;
            font-size: clamp(17px, 1.4vw, 20px);
            font-weight: 600;
            margin-top: 4px;
            overflow-wrap: anywhere;
        }
        .missed-detail-grid {
            align-items: start;
            display: grid;
            gap: 16px;
            grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
        }
        .missed-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }
        .missed-card-header {
            align-items: center;
            border-bottom: 1px solid var(--border);
            color: #0f172a;
            display: flex;
            font-size: 18px;
            font-weight: 600;
            gap: 10px;
            padding: 14px 16px;
        }
        .missed-card-header svg {
            color: #2563eb;
            height: 24px;
            width: 24px;
        }
        .missed-card-body { padding: 16px; }
        .visitor-info-list {
            display: grid;
            gap: 14px;
        }
        .visitor-info-row {
            align-items: center;
            display: grid;
            gap: 12px;
            grid-template-columns: 130px 24px minmax(0, 1fr);
        }
        .visitor-info-row span:first-child {
            color: #475569;
            font-weight: 600;
        }
        .visitor-info-icon {
            color: #2563eb;
            height: 20px;
            width: 20px;
        }
        .visitor-info-row strong {
            color: #0f172a;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
        .missed-message-box {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            color: #475569;
            display: flex;
            gap: 14px;
            min-height: 64px;
            padding: 14px 16px;
            white-space: pre-wrap;
        }
        .missed-message-box svg {
            color: #94a3b8;
            flex: 0 0 auto;
            height: 34px;
            width: 34px;
        }
        .right-stack { display: grid; gap: 16px; }
        .missed-form-grid { display: grid; gap: 14px; }
        .missed-form-top {
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(220px, 300px) minmax(0, 1fr);
        }
        .missed-field label {
            color: #0f172a;
            display: block;
            font-weight: 600;
            margin-bottom: 7px;
        }
        .missed-field select,
        .missed-field input,
        .missed-field textarea {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
            min-height: 42px;
            padding: 11px 12px;
            width: 100%;
        }
        .missed-field textarea {
            min-height: 118px;
            resize: vertical;
        }
        .missed-note-count {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
            text-align: right;
        }
        .missed-date-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .missed-date-field { position: relative; }
        .missed-date-field input {
            color: var(--text);
            padding-right: 42px;
        }
        .missed-date-field input:not(.has-value):not(:focus) { color: transparent; }
        .missed-date-placeholder {
            color: #64748b;
            font-size: 14px;
            left: 13px;
            pointer-events: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }
        .missed-date-field input.has-value + .missed-date-placeholder,
        .missed-date-field input:focus + .missed-date-placeholder { display: none; }
        .missed-reminder {
            align-items: center;
            display: inline-flex;
            gap: 8px;
            font-weight: 600;
        }
        .missed-reminder input {
            height: 18px;
            width: 18px;
        }
        .missed-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-end;
        }
        .missed-actions .btn.primary {
            align-items: center;
            display: inline-flex;
            gap: 8px;
        }
        .missed-actions svg { height: 17px; width: 17px; }
        @media (max-width: 1500px) {
            .missed-detail-top {
                align-items: end;
                grid-template-columns: auto minmax(0, 1fr);
            }
            .missed-top-field {
                border-left: 0;
                padding-left: 0;
            }
            .missed-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .missed-summary-icon {
                height: 46px;
                width: 46px;
            }
            .missed-summary-icon svg {
                height: 24px;
                width: 24px;
            }
            .missed-detail-grid {
                grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            }
            .visitor-info-row {
                grid-template-columns: 96px 22px minmax(0, 1fr);
            }
        }
        @media (max-width: 1199px) {
            .missed-detail-top,
            .missed-detail-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 1100px) {
            .missed-detail-top,
            .missed-summary-grid,
            .missed-detail-grid,
            .missed-form-top,
            .missed-date-grid {
                grid-template-columns: 1fr;
            }
            .missed-top-field {
                border-left: 0;
                padding-left: 0;
            }
            .missed-actions { justify-content: flex-start; }
        }
        @media (max-width: 767px) {
            .missed-detail-page { padding-top: 0; }
            .missed-detail-top {
                align-items: stretch;
                grid-template-columns: 1fr;
            }
            .missed-back { justify-content: center; width: 100%; }
            .missed-title h2 { font-size: 24px; }
            .missed-summary-card,
            .missed-message-box {
                align-items: flex-start;
            }
            .visitor-info-row {
                grid-template-columns: 1fr;
                gap: 7px;
            }
            .visitor-info-icon { display: none; }
            .missed-actions {
                display: grid;
                grid-template-columns: 1fr;
            }
            .missed-actions .btn { width: 100%; }
        }
        @media (max-width: 575px) {
            .missed-card-header,
            .missed-card-body { padding: 12px; }
            .missed-summary-card { padding: 12px; }
            .missed-summary-value { font-size: 18px; }
        }
    </style>

    <form method="POST" action="{{ route('agent.missed-chats.update', $lead) }}" class="missed-detail-page">
        @csrf
        @method('PATCH')

        <div class="missed-detail-top">
            <a class="missed-back" href="{{ route('agent.missed-chats') }}">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 18 9 12l6-6M10 12h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back
            </a>

            <div class="missed-title">
                <h2>Missed Chat</h2>
                <p>View and manage missed chat details and follow-up.</p>
            </div>

            <div class="missed-top-field">
                <label for="top_status">Status</label>
                <div class="missed-select-shell">
                    <span class="status-dot {{ $status }}" data-status-dot aria-hidden="true"></span>
                    <select id="top_status" data-status-mirror>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        <div class="missed-summary-grid">
            <div class="missed-summary-card assigned">
                <span class="missed-summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M7 3v4M17 3v4M4 8h16M5 5h14v15H5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <div class="missed-summary-title">Assigned At</div>
                    <div class="missed-summary-value">{{ $assignedAt }}</div>
                </div>
            </div>

            <div class="missed-summary-card contacted">
                <span class="missed-summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M8 5 6 7c-1 1-1 3 0 5a17 17 0 0 0 6 6c2 1 4 1 5 0l2-2-4-4-2 2c-2-1-3-2-4-4l2-2-3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <div class="missed-summary-title">Last Contacted</div>
                    <div class="missed-summary-value">{{ $lastContacted }}</div>
                </div>
            </div>

            <div class="missed-summary-card next">
                <span class="missed-summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M12 8v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <div class="missed-summary-title">Next Follow-up</div>
                    <div class="missed-summary-value">{{ $nextFollowup }}</div>
                </div>
            </div>

            <div class="missed-summary-card status">
                <span class="missed-summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <div>
                    <div class="missed-summary-title">Current Status</div>
                    <div class="missed-summary-value">{{ $statusLabel }}</div>
                </div>
            </div>
        </div>

        <div class="missed-detail-grid">
            <section class="missed-card">
                <div class="missed-card-header">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    Visitor Information
                </div>
                <div class="missed-card-body">
                    <div class="visitor-info-list">
                        @foreach ([
                            ['Name', $lead->visitor?->displayName() ?? ($lead->name ?: 'Unknown Visitor'), 'user'],
                            ['Phone', $lead->visitor?->phone ?: ($lead->phone ?: 'No phone'), 'phone'],
                            ['Email', $lead->visitor?->email ?: ($lead->email ?: 'No email'), 'email'],
                            ['Website', $lead->website?->name ?? 'Unknown website', 'website'],
                            ['Source', 'Live Chat', 'message'],
                            ['Assigned By', $lead->assignedBy?->name ?? 'Admin', 'user'],
                            ['Assigned At', $assignedAt, 'calendar'],
                        ] as [$label, $value, $icon])
                            <div class="visitor-info-row">
                                <span>{{ $label }}</span>
                                <svg class="visitor-info-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    @switch($icon)
                                        @case('phone')
                                            <path d="M8 5 6 7c-1 1-1 3 0 5a17 17 0 0 0 6 6c2 1 4 1 5 0l2-2-4-4-2 2c-2-1-3-2-4-4l2-2-3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            @break
                                        @case('email')
                                            <path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="m5 7 7 6 7-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            @break
                                        @case('website')
                                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            @break
                                        @case('calendar')
                                            <path d="M7 3v4M17 3v4M4 8h16M5 5h14v15H5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            @break
                                        @case('message')
                                            <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            @break
                                        @default
                                            <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    @endswitch
                                </svg>
                                <strong>{{ $value }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="right-stack">
                <section class="missed-card">
                    <div class="missed-card-header">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Original Visitor Message
                    </div>
                    <div class="missed-card-body">
                        <div class="missed-message-box">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 3h7l5 5v13H7z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M14 3v6h5M10 14h6M10 17h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            {{ $lead->notes ?: 'No message available from visitor.' }}
                        </div>
                    </div>
                </section>

                <section class="missed-card">
                    <div class="missed-card-header">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Agent Note
                    </div>
                    <div class="missed-card-body missed-form-grid">
                        <input id="followup_status" name="followup_status" type="hidden" value="{{ $status }}" data-status-target>

                        <div class="missed-field">
                            <label for="agent_note">Agent Note</label>
                            <textarea id="agent_note" name="agent_note" maxlength="5000" placeholder="Write follow-up notes here...">{{ old('agent_note', $lead->agent_note) }}</textarea>
                            <div class="missed-note-count">0/1000</div>
                        </div>

                        <div class="missed-date-grid">
                            <div class="missed-field">
                                <label for="last_contacted_at">Last Contacted At</label>
                                <div class="missed-date-field">
                                    <input
                                        id="last_contacted_at"
                                        name="last_contacted_at"
                                        type="datetime-local"
                                        max="{{ $maxDateTime }}"
                                        value="{{ old('last_contacted_at', $lead->last_contacted_at ? \App\Support\BrowserTime::format($lead->last_contacted_at, 'Y-m-d\TH:i') : null) }}"
                                        data-clean-date-input
                                    >
                                    <span class="missed-date-placeholder">Select date and time</span>
                                </div>
                            </div>

                            <div class="missed-field">
                                <label for="next_followup_at">Next Follow-up At</label>
                                <div class="missed-date-field">
                                    <input
                                        id="next_followup_at"
                                        name="next_followup_at"
                                        type="datetime-local"
                                        max="{{ $maxDateTime }}"
                                        value="{{ old('next_followup_at', $lead->next_followup_at ? \App\Support\BrowserTime::format($lead->next_followup_at, 'Y-m-d\TH:i') : null) }}"
                                        data-clean-date-input
                                    >
                                    <span class="missed-date-placeholder">Select date and time</span>
                                </div>
                            </div>
                        </div>

                        <label class="missed-reminder">
                            <input type="hidden" name="remind_before_followup" value="0">
                            <input
                                type="checkbox"
                                name="remind_before_followup"
                                value="1"
                                @checked(old('remind_before_followup', $lead->followup_reminder_enabled))
                            >
                            Remind me before follow-up
                        </label>

                        <div class="missed-actions">
                            <a class="btn gray" href="{{ route('agent.missed-chats') }}">Cancel</a>
                            <button class="btn primary" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m22 2-7 20-4-9-9-4 20-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Save Follow-up
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>

    <script>
        document.querySelectorAll('[data-clean-date-input]').forEach(input => {
            const sync = () => input.classList.toggle('has-value', Boolean(input.value));

            sync();
            input.addEventListener('input', sync);
            input.addEventListener('change', sync);
        });

        document.querySelectorAll('[data-status-mirror]').forEach(select => {
            const target = document.querySelector('[data-status-target]');
            const dot = select.closest('.missed-select-shell')?.querySelector('[data-status-dot]');
            const statusClasses = ['contacted', 'follow_up_required', 'resolved', 'unable_to_reach'];

            const syncStatusDot = () => {
                if (!dot) {
                    return;
                }

                dot.classList.remove(...statusClasses);
                dot.classList.add(select.value);
            };

            select.addEventListener('change', () => {
                if (target) {
                    target.value = select.value;
                }

                syncStatusDot();
            });

            syncStatusDot();
        });
    </script>
@endsection
