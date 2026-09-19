@extends('agent.layout', ['title' => 'Canned Replies'])

@section('content')
    <style>
        .canned-page-header {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: clamp(16px, 1.6vw, 24px);
        }
        .canned-page-title h2 {
            color: #0f172a;
            font-size: clamp(24px, 2vw, 30px);
            font-weight: 600;
            margin: 0;
        }
        .canned-page-title p {
            color: #64748b;
            font-size: 15px;
            font-weight: 600;
            margin: 6px 0 0;
        }
        .reply-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
        }
        .reply-panel-header {
            align-items: center;
            display: flex;
            gap: 14px;
            min-width: 0;
        }
        .reply-panel-icon {
            align-items: center;
            background: #eaf3ff;
            border-radius: 999px;
            color: #2563eb;
            display: inline-flex;
            flex: 0 0 auto;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .reply-panel-icon svg {
            display: block;
            height: 24px;
            width: 24px;
        }
        .reply-panel-header strong {
            color: #0f172a;
            display: block;
            font-size: 20px;
            font-weight: 600;
            line-height: 1.2;
        }
        .reply-panel-text {
            display: block;
        }
        .reply-panel-text > span {
            color: #64748b;
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-top: 4px;
        }
        .reply-toolbar {
            align-items: center;
            display: flex;
            gap: 12px;
            margin-left: auto;
        }
        .reply-search {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            gap: 8px;
            min-width: min(280px, 100%);
            padding: 0 12px;
        }
        .reply-search svg { color: #64748b; height: 18px; width: 18px; }
        .reply-search input {
            border: 0;
            font: inherit;
            outline: 0;
            padding: 12px 0;
            width: 100%;
        }
        .reply-form {
            display: grid;
            gap: 20px;
        }
        .form-group {
            display: grid;
            gap: 8px;
        }
        .form-group label,
        .checkbox-row {
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
        }
        .required { color: #ef4444; }
        .form-help {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }
        .form-control {
            background: #fff;
            border: 1px solid #d7dee8;
            border-radius: 8px;
            color: var(--text);
            font: inherit;
            padding: 12px;
            width: 100%;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
            outline: none;
        }
        .reply-message-field {
            min-height: 128px;
        }
        .reply-tip {
            align-items: center;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            color: #334155;
            display: flex;
            font-size: 13px;
            font-weight: 600;
            gap: 8px;
            padding: 12px;
        }
        .reply-tip svg { color: #f59e0b; height: 18px; width: 18px; }
        .checkbox-row {
            align-items: flex-start;
            display: inline-flex;
            gap: 10px;
        }
        .checkbox-row input {
            accent-color: var(--primary);
            height: 19px;
            margin-top: 2px;
            width: 19px;
        }
        .checkbox-row small {
            color: #64748b;
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }
        .reply-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .reply-submit {
            align-items: center;
            display: inline-flex;
            gap: 8px;
        }
        .reply-submit svg { height: 17px; width: 17px; }
        .reply-list {
            display: grid;
            gap: 10px;
        }
        .reply-card {
            align-items: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(0, 1fr) auto;
            padding: 16px;
        }
        .reply-card > div {
            min-width: 0;
        }
        .reply-card-title {
            color: #0f172a;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .reply-message {
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.45;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }
        .reply-card-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }
        .reply-chip,
        .reply-status {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 13px;
            font-weight: 600;
            gap: 7px;
            padding: 8px 12px;
            white-space: nowrap;
        }
        .reply-chip { background: #eaf3ff; color: #2563eb; }
        .reply-status.active { background: #dcfce7; color: #15803d; }
        .reply-status.inactive { background: #e5e7eb; color: #475569; }
        .reply-status-dot {
            background: currentColor;
            border-radius: 999px;
            height: 8px;
            width: 8px;
        }
        .reply-icon-button {
            align-items: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #334155;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            width: 38px;
        }
        .reply-icon-button:hover {
            border-color: #93c5fd;
            color: #2563eb;
        }
        .reply-icon-button.danger {
            background: #fff1f2;
            color: #ef4444;
        }
        .reply-icon-button svg { height: 18px; width: 18px; }
        .reply-count {
            color: #64748b;
            font-weight: 600;
            margin-top: 16px;
        }
        @media (max-width: 1500px) {
            .reply-grid {
                grid-template-columns: minmax(300px, 380px) minmax(0, 1fr);
            }
            .reply-panel-icon {
                height: 42px;
                width: 42px;
            }
            .reply-panel-header strong {
                font-size: 18px;
            }
            .reply-card {
                padding: 14px;
            }
            .reply-chip,
            .reply-status {
                font-size: 12px;
                padding: 7px 10px;
            }
        }
        @media (max-width: 1100px) {
            .reply-grid { grid-template-columns: 1fr; }
            .reply-toolbar { margin-left: 0; }
            .reply-grid .panel-header { align-items: flex-start; flex-direction: column; }
        }
        @media (max-width: 700px) {
            .canned-page-header,
            .reply-card,
            .reply-card-actions {
                align-items: stretch;
                grid-template-columns: 1fr;
            }
            .canned-page-header { display: grid; gap: 12px; }
            .reply-search { min-width: 0; width: 100%; }
            .reply-toolbar,
            .reply-actions,
            .reply-card-actions {
                width: 100%;
            }
            .reply-actions .btn,
            .reply-submit {
                justify-content: center;
                width: 100%;
            }
            .reply-icon-button {
                flex: 1 1 auto;
            }
        }
        @media (max-width: 575px) {
            .canned-page-title h2 { font-size: 26px; }
            .reply-panel-header {
                align-items: flex-start;
            }
            .reply-panel-icon {
                height: 42px;
                width: 42px;
            }
            .reply-card {
                padding: 12px;
            }
            .reply-tip {
                align-items: flex-start;
            }
        }
    </style>

    @if ($websites->isEmpty())
        <section class="panel">
            <div class="panel-body muted">
                No assigned websites.
            </div>
        </section>
    @else
        <div class="canned-page-header">
            <div class="canned-page-title">
                <h2>Canned Replies</h2>
                <p>Create and manage your quick replies to save time and respond faster.</p>
            </div>
        </div>

        <div class="reply-grid">
            <section class="panel">
                <div class="panel-header">
                    <div class="reply-panel-header">
                        <span class="reply-panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="reply-panel-text">
                            <strong>{{ $editingReply ? 'Edit Reply' : 'Create New Reply' }}</strong>
                            <span>Save frequently used messages for quick access.</span>
                        </span>
                    </div>
                </div>

                <div class="panel-body">
                    <form
                        class="reply-form"
                        method="POST"
                        action="{{ $editingReply
                            ? route('agent.canned-replies.update', ['reply' => $editingReply, 'website' => $selectedWebsite->id])
                            : route('agent.canned-replies.store', ['website' => $selectedWebsite->id]) }}"
                    >
                        @csrf
                        @if ($editingReply)
                            @method('PATCH')
                        @endif

                        <div class="form-group">
                            <label for="website">Website</label>
                            <select
                                id="website"
                                class="form-control"
                                onchange="window.location.href = this.value"
                            >
                                @foreach ($websites as $website)
                                    <option
                                        value="{{ route('agent.canned-replies', ['website' => $website->id]) }}"
                                        @selected($website->id === $selectedWebsite->id)
                                    >
                                        {{ $website->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-help">Select the website for this reply.</div>
                        </div>

                        <div class="form-group">
                            <label for="title">Title <span class="required">*</span></label>
                            <input
                                id="title"
                                class="form-control"
                                name="title"
                                type="text"
                                maxlength="255"
                                required
                                value="{{ old('title', $editingReply?->title) }}"
                                placeholder="Enter a short title"
                            >
                            <div class="form-help">Example: Greeting, Working Hours, Pricing, etc.</div>
                            @error('title')
                                <div class="item-sub" style="color: var(--danger);">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea
                                id="message"
                                class="form-control reply-message-field"
                                name="message"
                                maxlength="5000"
                                rows="6"
                                required
                                placeholder="Type your canned reply message here..."
                            >{{ old('message', $editingReply?->message) }}</textarea>
                            @error('message')
                                <div class="item-sub" style="color: var(--danger);">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="reply-tip">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M9 18h6M10 22h4M8 14a6 6 0 1 1 8 0c-1 1-1.5 2-1.5 3h-5c0-1-.5-2-1.5-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Tip: Use short and clear titles so you can quickly find this reply later.</span>
                        </div>

                        <label class="checkbox-row">
                            <input
                                name="is_active"
                                type="checkbox"
                                value="1"
                                @checked(old('is_active', $editingReply?->is_active ?? true))
                            >
                            <span>
                                Active
                                <small>Inactive replies won't be shown in the quick reply list.</small>
                            </span>
                        </label>

                        <div class="reply-actions">
                            <button class="btn primary reply-submit" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m22 2-7 20-4-9-9-4 20-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ $editingReply ? 'Update Reply' : 'Create Reply' }}
                            </button>

                            <a
                                class="btn gray"
                                href="{{ route('agent.canned-replies', ['website' => $selectedWebsite->id]) }}"
                            >
                                {{ $editingReply ? 'Cancel' : 'Clear' }}
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <div class="reply-panel-header">
                        <span class="reply-panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M5 6h14v10H9l-4 3V6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 10h6M9 13h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="reply-panel-text">
                            <strong>Saved Replies</strong>
                            <span>Manage your existing canned replies.</span>
                        </span>
                    </div>

                    <div class="reply-toolbar">
                        <label class="reply-search">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                <path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <input type="search" placeholder="Search replies..." data-reply-search>
                        </label>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="reply-list" data-reply-list>
                        @forelse ($replies as $reply)
                            <div class="reply-card" data-reply-card data-search-text="{{ \Illuminate\Support\Str::lower($reply->title . ' ' . $reply->message) }}">
                                <div>
                                    <div class="reply-card-title">{{ $reply->title }}</div>
                                    <div class="reply-message">{{ \Illuminate\Support\Str::limit($reply->message, 220) }}</div>
                                </div>

                                <div class="reply-card-actions">
                                    <span class="reply-chip">{{ $selectedWebsite->name }}</span>
                                    <span @class(['reply-status', 'active' => $reply->is_active, 'inactive' => ! $reply->is_active])>
                                        <span class="reply-status-dot"></span>
                                        {{ $reply->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <a
                                        class="reply-icon-button"
                                        href="{{ route('agent.canned-replies', ['website' => $selectedWebsite->id, 'edit' => $reply->id]) }}"
                                        title="Edit"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <button class="reply-icon-button" type="button" title="Copy" data-copy-reply="{{ e($reply->message) }}">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 8h11v11H8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M5 16H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    <form
                                        method="POST"
                                        action="{{ route('agent.canned-replies.delete', ['reply' => $reply, 'website' => $selectedWebsite->id]) }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="reply-icon-button danger" type="submit" title="Delete">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="muted">No canned replies yet.</div>
                        @endforelse
                    </div>

                    <div class="reply-count">Showing {{ $replies->count() }} replies</div>
                </div>
            </section>
        </div>

        <script>
            document.querySelectorAll('[data-reply-search]').forEach(input => {
                const cards = Array.from(document.querySelectorAll('[data-reply-card]'));

                input.addEventListener('input', () => {
                    const term = input.value.trim().toLowerCase();

                    cards.forEach(card => {
                        card.hidden = term !== '' && !card.dataset.searchText.includes(term);
                    });
                });
            });

            document.querySelectorAll('[data-copy-reply]').forEach(button => {
                button.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(button.dataset.copyReply || '');
                    } catch (error) {
                        // Clipboard can be blocked by the browser; the copy action is optional.
                    }
                });
            });
        </script>
    @endif
@endsection
