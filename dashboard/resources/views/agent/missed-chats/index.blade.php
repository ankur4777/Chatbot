@extends('agent.layout', ['title' => 'Missed Chats'])

@section('content')
    <style>
        .missed-page-header {
            align-items: flex-start;
            display: flex;
            justify-content: space-between;
            margin-bottom: clamp(12px, 1.2vw, 18px);
        }
        .missed-page-title h2 {
            color: #0f172a;
            font-size: clamp(23px, 1.85vw, 28px);
            font-weight: 600;
            margin: 0;
        }
        .missed-page-title p {
            color: #64748b;
            font-size: 14.5px;
            font-weight: 600;
            margin: 4px 0 0;
        }
        .missed-stat-grid {
            display: grid;
            gap: clamp(10px, 1vw, 16px);
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: clamp(12px, 1.2vw, 18px);
        }
        .missed-stat-card {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 10px;
            display: flex;
            gap: clamp(10px, .95vw, 16px);
            min-width: 0;
            padding: clamp(11px, 1.1vw, 16px);
        }
        .missed-stat-card.total { background: #eff6ff; border-color: #bfdbfe; }
        .missed-stat-card.pending { background: #fffbeb; border-color: #fde68a; }
        .missed-stat-card.contacted { background: #f0fdf4; border-color: #bbf7d0; }
        .missed-stat-card.resolved { background: #faf5ff; border-color: #e9d5ff; }
        .missed-stat-icon {
            align-items: center;
            border-radius: 10px;
            display: inline-flex;
            flex: 0 0 auto;
            height: 56px;
            justify-content: center;
            width: 56px;
        }
        .missed-stat-icon svg { height: 28px; width: 28px; }
        .missed-stat-card.total .missed-stat-icon { background: #dbeafe; color: #2563eb; }
        .missed-stat-card.pending .missed-stat-icon { background: #fef3c7; color: #d97706; }
        .missed-stat-card.contacted .missed-stat-icon { background: #dcfce7; color: #16a34a; }
        .missed-stat-card.resolved .missed-stat-icon { background: #f3e8ff; color: #7c3aed; }
        .missed-stat-title {
            color: #475569;
            font-size: 14.5px;
            font-weight: 600;
        }
        .missed-stat-value {
            color: #0f172a;
            font-size: clamp(23px, 1.75vw, 28px);
            font-weight: 600;
            line-height: 1.1;
            margin-top: 4px;
        }
        .missed-stat-sub {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }
        .missed-panel-title {
            align-items: center;
            display: flex;
            gap: 12px;
        }
        .missed-panel-icon {
            align-items: center;
            background: #eaf3ff;
            border-radius: 999px;
            color: #2563eb;
            display: inline-flex;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .missed-panel-icon svg { height: 24px; width: 24px; }
        .missed-panel-title strong {
            color: #0f172a;
            display: block;
            font-size: 19px;
            font-weight: 600;
        }
        .missed-panel-copy {
            display: block;
        }
        .missed-panel-copy span {
            color: #64748b;
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }
        .missed-toolbar {
            align-items: end;
            display: flex;
            gap: 14px;
            margin-left: auto;
        }
        .missed-search {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            gap: 8px;
            min-width: 320px;
            padding: 0 12px;
        }
        .missed-search svg { color: #64748b; height: 18px; width: 18px; }
        .missed-search input {
            border: 0;
            font: inherit;
            outline: 0;
            padding: 10px 0;
            width: 100%;
        }
        .missed-filter {
            display: grid;
            gap: 6px;
        }
        .missed-filter label {
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }
        .missed-filter select {
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
            min-width: 160px;
            padding: 10px 12px;
        }
        .missed-table-wrap { overflow: visible; }
        .missed-table {
            border-collapse: collapse;
            width: 100%;
        }
        .missed-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            padding: 14px 16px;
            text-align: left;
        }
        .missed-table td {
            border-top: 1px solid #e5e7eb;
            color: #0f172a;
            font-size: 16px;
            font-weight: 600;
            padding: 16px;
            vertical-align: middle;
        }
        .visitor-cell {
            align-items: center;
            display: flex;
            gap: 12px;
        }
        .visitor-avatar {
            align-items: center;
            background: #fce7f3;
            border-radius: 999px;
            color: #db2777;
            display: inline-flex;
            flex: 0 0 auto;
            font-weight: 600;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .visitor-name { font-size: 16px; font-weight: 600; }
        .visitor-type { color: #64748b; font-size: 15px; margin-top: 4px; }
        .contact-line {
            align-items: center;
            color: #0f172a;
            display: flex;
            gap: 8px;
            margin: 4px 0;
        }
        .contact-line svg {
            color: #64748b;
            height: 16px;
            width: 16px;
        }
        .message-muted { color: #64748b; }
        .message-line { color: #2563eb; display: block; margin-top: 4px; }
        .assigned-date strong { display: block; font-weight: 600; }
        .assigned-date span { color: #64748b; display: block; font-size: 15px; font-weight: 600; margin-top: 4px; }
        .status-pill {
            align-items: center;
            border-radius: 8px;
            display: inline-flex;
            font-size: 15px;
            font-weight: 600;
            gap: 8px;
            padding: 9px 12px;
            white-space: nowrap;
        }
        .status-pill.assigned { background: #dbeafe; color: #1d4ed8; }
        .status-pill.follow-up-required { background: #fef3c7; color: #92400e; }
        .status-pill.contacted { background: #dcfce7; color: #166534; }
        .status-pill.resolved { background: #dbeafe; color: #1d4ed8; }
        .status-pill.unable-to-reach { background: #fee2e2; color: #991b1b; }
        .status-pill-dot {
            background: currentColor;
            border-radius: 999px;
            height: 9px;
            width: 9px;
        }
        .open-btn {
            align-items: center;
            background: #eaf3ff;
            border: 0;
            border-radius: 8px;
            color: #2563eb;
            display: inline-flex;
            font-size: 16px;
            font-weight: 600;
            gap: 8px;
            padding: 10px 14px;
        }
        .open-btn svg { height: 17px; width: 17px; }
        .missed-pagination {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
            margin-top: 16px;
        }
        .missed-page-buttons { display: flex; gap: 8px; }
        @media (max-width: 1500px) {
            .missed-stat-icon {
                height: 48px;
                width: 48px;
            }
            .missed-stat-icon svg {
                height: 25px;
                width: 25px;
            }
            .missed-stat-title,
            .missed-stat-sub {
                font-size: 13px;
            }
            .missed-table th,
            .missed-table td {
                font-size: 14px;
                padding: 12px;
            }
            .visitor-name,
            .visitor-type,
            .assigned-date span,
            .status-pill,
            .open-btn {
                font-size: 14px;
            }
            .status-pill,
            .open-btn {
                padding: 8px 10px;
            }
            .missed-toolbar {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .missed-search {
                min-width: min(280px, 100%);
            }
        }
        @media (max-width: 1199px) {
            .missed-table,
            .missed-table tbody,
            .missed-table tr,
            .missed-table td {
                display: block;
                width: 100%;
            }
            .missed-table thead {
                display: none;
            }
            .missed-table tbody {
                display: grid;
                gap: 12px;
            }
            .missed-table tr {
                border: 1px solid var(--border);
                border-radius: 10px;
                overflow: hidden;
            }
            .missed-table td {
                align-items: flex-start;
                border-top: 0;
                border-bottom: 1px solid #e5e7eb;
                display: grid;
                gap: 10px;
                grid-template-columns: 120px minmax(0, 1fr);
                padding: 12px;
            }
            .missed-table td:last-child {
                border-bottom: 0;
            }
            .missed-table td::before {
                color: #64748b;
                content: attr(data-label);
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .02em;
                text-transform: uppercase;
            }
            .missed-table td[colspan] {
                display: block;
            }
            .missed-table td[colspan]::before {
                content: none;
            }
            .missed-toolbar {
                align-items: stretch;
                margin-left: 0;
                width: 100%;
            }
            .missed-filter,
            .missed-filter select {
                width: 100%;
            }
        }
        @media (max-width: 1100px) {
            .missed-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .missed-panel-header { align-items: flex-start; flex-direction: column; }
            .missed-toolbar { align-items: stretch; margin-left: 0; width: 100%; }
            .missed-search { min-width: 0; width: 100%; }
        }
        @media (max-width: 640px) {
            .missed-page-header,
            .missed-toolbar { display: grid; }
            .missed-stat-grid { grid-template-columns: 1fr; }
            .missed-page-title h2 { font-size: 26px; }
            .missed-stat-card { padding: 14px; }
            .missed-panel-title { align-items: flex-start; }
            .missed-panel-icon {
                height: 44px;
                width: 44px;
            }
            .missed-pagination {
                align-items: stretch;
                flex-direction: column;
            }
            .missed-page-buttons {
                justify-content: flex-end;
            }
            .missed-table td {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="missed-page-header">
        <div class="missed-page-title">
            <h2>Missed Chats</h2>
            <p>Here are the missed chat requests assigned to you. (Last 30 Days)</p>
        </div>

    </div>

    <div class="missed-stat-grid">
        <div class="missed-stat-card total">
            <span class="missed-stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </span>
            <div>
                <div class="missed-stat-title">Total Missed Chats</div>
                <div class="missed-stat-value">{{ $missedStats['total'] ?? 0 }}</div>
                <div class="missed-stat-sub">Assigned to you</div>
            </div>
        </div>

        <div class="missed-stat-card pending">
            <span class="missed-stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M7 3h10M7 21h10M8 3v4a4 4 0 0 0 8 0V3M8 21v-4a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div>
                <div class="missed-stat-title">Follow-up Required</div>
                <div class="missed-stat-value">{{ $missedStats['follow_up_required'] ?? 0 }}</div>
                <div class="missed-stat-sub">Needs attention</div>
            </div>
        </div>

        <div class="missed-stat-card contacted">
            <span class="missed-stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M8 5 6 7c-1 1-1 3 0 5a17 17 0 0 0 6 6c2 1 4 1 5 0l2-2-4-4-2 2c-2-1-3-2-4-4l2-2-3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div>
                <div class="missed-stat-title">Contacted</div>
                <div class="missed-stat-value">{{ $missedStats['contacted'] ?? 0 }}</div>
                <div class="missed-stat-sub">You have contacted</div>
            </div>
        </div>

        <div class="missed-stat-card resolved">
            <span class="missed-stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m8 12 3 3 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div>
                <div class="missed-stat-title">Resolved</div>
                <div class="missed-stat-value">{{ $missedStats['resolved'] ?? 0 }}</div>
                <div class="missed-stat-sub">Successfully handled</div>
            </div>
        </div>
    </div>

    <section class="panel">
        <div class="panel-header missed-panel-header">
            <div class="missed-panel-title">
                <span class="missed-panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="missed-panel-copy">
                    <strong>Assigned Missed Chats</strong>
                    <span>Follow up with the missed chat requests assigned to you.</span>
                </span>
            </div>

            <div class="missed-toolbar">
                <label class="missed-search">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input type="search" placeholder="Search by name, email, or phone..." data-missed-search>
                </label>

                <div class="missed-filter">
                    <label for="missed-status-filter">Status</label>
                    <select id="missed-status-filter" data-missed-status-filter>
                        <option value="">All Status</option>
                        <option value="assigned">Assigned</option>
                        <option value="follow-up-required">Follow-up Required</option>
                        <option value="contacted">Contacted</option>
                        <option value="resolved">Resolved</option>
                        <option value="unable-to-reach">Unable to Reach</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="panel-body">
            <div class="missed-table-wrap">
                <table class="missed-table">
                    <thead>
                        <tr>
                            <th>Visitor</th>
                            <th>Contact Info</th>
                            <th>Website</th>
                            <th>Message</th>
                            <th>Assigned</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody data-missed-table>
                        @forelse ($leads as $lead)
                            @php
                                $normalizedStatus = ($lead->followup_status ?? 'assigned') === 'pending'
                                    ? 'assigned'
                                    : ($lead->followup_status ?? 'assigned');
                                $statusLabel = \App\Models\ChatbotLead::followupStatusLabels()[$normalizedStatus] ?? 'Follow-up Required';
                                $statusClass = str_replace('_', '-', $normalizedStatus);
                                $visitorName = $lead->visitor?->displayName() ?? ($lead->name ?: 'Unknown Visitor');
                                $searchText = \Illuminate\Support\Str::lower($visitorName . ' ' . $lead->email . ' ' . $lead->phone . ' ' . $lead->website?->name . ' ' . $lead->notes);
                            @endphp
                            <tr data-missed-row data-search-text="{{ $searchText }}" data-status="{{ $statusClass }}">
                                <td data-label="Visitor">
                                    <div class="visitor-cell">
                                        <span class="visitor-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($visitorName, 0, 1)) }}</span>
                                        <span>
                                            <span class="visitor-name">{{ $visitorName }}</span>
                                            <span class="visitor-type">Visitor</span>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Contact Info">
                                    <div class="contact-line">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="m5 7 7 6 7-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $lead->email ?: 'No email' }}
                                    </div>
                                    <div class="contact-line">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 5 6 7c-1 1-1 3 0 5a17 17 0 0 0 6 6c2 1 4 1 5 0l2-2-4-4-2 2c-2-1-3-2-4-4l2-2-3-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $lead->phone ?: 'No phone' }}
                                    </div>
                                </td>
                                <td data-label="Website">{{ $lead->website?->name ?? 'Unknown website' }}</td>
                                <td data-label="Message">
                                    <span class="message-muted">{{ \Illuminate\Support\Str::limit($lead->notes ?: 'No message', 80) }}</span>
                                    <span class="message-line">—</span>
                                </td>
                                <td data-label="Assigned">
                                    <span class="assigned-date">
                                        <strong>{{ $lead->assigned_at ? \App\Support\BrowserTime::format($lead->assigned_at, 'd M Y') : 'N/A' }}</strong>
                                        <span>{{ $lead->assigned_at ? \App\Support\BrowserTime::format($lead->assigned_at, 'h:i A') : '' }}</span>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="status-pill {{ $statusClass }}">
                                        <span class="status-pill-dot"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <a class="open-btn" href="{{ route('agent.missed-chats.show', $lead) }}">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                        </svg>
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="muted">No missed chats assigned to you.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="missed-pagination">
                <div class="muted">
                    Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }} missed chats
                </div>

                <div class="missed-page-buttons">
                    @if ($leads->onFirstPage())
                        <span class="btn gray" style="cursor: not-allowed; opacity: .55;">Previous</span>
                    @else
                        <a class="btn gray" href="{{ $leads->previousPageUrl() }}">Previous</a>
                    @endif

                    @if ($leads->hasMorePages())
                        <a class="btn primary" href="{{ $leads->nextPageUrl() }}">Next</a>
                    @else
                        <span class="btn gray" style="cursor: not-allowed; opacity: .55;">Next</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const search = document.querySelector('[data-missed-search]');
            const statusFilter = document.querySelector('[data-missed-status-filter]');
            const rows = Array.from(document.querySelectorAll('[data-missed-row]'));

            const applyFilters = () => {
                const term = (search?.value || '').trim().toLowerCase();
                const status = statusFilter?.value || '';

                rows.forEach(row => {
                    const matchesSearch = term === '' || row.dataset.searchText.includes(term);
                    const matchesStatus = status === '' || row.dataset.status === status;
                    row.hidden = !matchesSearch || !matchesStatus;
                });
            };

            search?.addEventListener('input', applyFilters);
            statusFilter?.addEventListener('change', applyFilters);
        })();
    </script>
@endsection
