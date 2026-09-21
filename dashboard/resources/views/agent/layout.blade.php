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
        *::before,
        *::after { box-sizing: border-box; }
        html,
        body {
            height: 100%;
        }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            overflow: hidden;
        }
        img,
        video,
        svg { max-width: 100%; }
        input,
        select,
        textarea,
        button { max-width: 100%; }
        a { color: inherit; text-decoration: none; }
        .shell {
            display: grid;
            grid-template-columns: clamp(212px, 14vw, 240px) minmax(0, 1fr);
            height: 100vh;
            min-height: 0;
            overflow: hidden;
        }
        .sidebar {
            border-right: 1px solid var(--border);
            background: #0f172a;
            color: #e5e7eb;
            height: 100vh;
            min-height: 0;
            overflow-y: auto;
            padding: 20px 16px;
            position: sticky;
            top: 0;
        }
        .brand {
            align-items: center;
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }
        .brand-icon {
            color: #f59e0b;
            flex: 0 0 auto;
            height: 24px;
            width: 24px;
        }
        .brand-title { font-size: 18px; font-weight: 700; line-height: 1.1; }
        .brand-subtitle { color: #94a3b8; font-size: 12px; margin-top: 3px; }
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
        .nav-label span:last-child {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .nav a.active {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }
        .nav a:hover, .nav button:hover { background: #1e293b; color: #fff; }
        .nav a.active:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .nav-label {
            align-items: center;
            display: inline-flex;
            gap: 10px;
            min-width: 0;
        }
        .nav-icon {
            color: currentColor;
            flex: 0 0 auto;
            height: 18px;
            opacity: .9;
            width: 18px;
        }
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
        .logout-modal-backdrop {
            align-items: center;
            background: rgba(15, 23, 42, 0.58);
            display: none;
            inset: 0;
            justify-content: center;
            padding: 20px;
            position: fixed;
            z-index: 80;
        }
        .logout-modal-backdrop.open { display: flex; }
        .logout-modal {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
            max-width: 420px;
            padding: 22px;
            width: 100%;
        }
        .logout-modal h2 {
            font-size: 20px;
            margin: 0 0 8px;
        }
        .logout-modal p {
            color: var(--muted);
            line-height: 1.5;
            margin: 0;
        }
        .logout-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
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
        .content {
            display: flex;
            flex-direction: column;
            height: 100vh;
            min-width: 0;
            overflow-x: hidden;
            overflow-y: auto;
            padding: clamp(14px, 1.2vw, 22px) clamp(18px, 1.6vw, 28px) clamp(18px, 1.7vw, 28px);
            position: relative;
        }
        .topbar {
            align-items: center;
            display: flex;
            flex-shrink: 0;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .topbar.compact {
            margin-bottom: 0;
            min-height: 0;
            position: absolute;
            right: 24px;
            top: 14px;
            z-index: 5;
        }
        .topbar-spacer { min-height: 1px; }
        .topbar-actions {
            align-items: center;
            display: flex;
            gap: 14px;
            margin-left: auto;
        }
        .agent-notification-button {
            align-items: center;
            background: transparent;
            border: 0;
            color: #0f172a;
            cursor: pointer;
            display: inline-flex;
            height: 40px;
            justify-content: center;
            padding: 0;
            position: relative;
            width: 40px;
        }
        .agent-notification-button svg {
            height: 22px;
            width: 22px;
        }
        .agent-notification-badge {
            align-items: center;
            background: #ef4444;
            border: 2px solid #fff;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 10px;
            font-weight: 800;
            height: 18px;
            justify-content: center;
            line-height: 1;
            min-width: 18px;
            padding: 0 4px;
            position: absolute;
            right: 3px;
            top: 2px;
        }
        .agent-notification-badge[hidden] { display: none; }
        .agent-notification-wrap { position: relative; }
        .agent-notification-dropdown {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .16);
            display: grid;
            gap: 8px;
            max-width: calc(100vw - 28px);
            padding: 10px;
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 360px;
            z-index: 70;
        }
        .agent-notification-dropdown[hidden] { display: none; }
        .agent-notification-dropdown-header {
            align-items: center;
            display: flex;
            justify-content: space-between;
            padding: 4px 4px 8px;
        }
        .agent-notification-dropdown-header strong { color: var(--text); font-size: 15px; }
        .agent-notification-dropdown-header button {
            background: transparent;
            border: 0;
            color: #2563eb;
            cursor: pointer;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
        }
        .agent-notification-dropdown-list { display: grid; gap: 6px; max-height: 330px; overflow: auto; }
        .agent-notification-mini {
            border: 1px solid #e5edf7;
            border-radius: 8px;
            color: inherit;
            display: grid;
            gap: 3px;
            padding: 10px;
            text-decoration: none;
        }
        .agent-notification-mini.unread { background: #c2dbfc; border-color: #bfdbfe; }
        .agent-notification-mini strong { color: #0f172a; font-size: 13px; }
        .agent-notification-mini span { color: #64748b; font-size: 12px; line-height: 1.35; }
        .agent-notification-dropdown-footer {
            border-top: 1px solid var(--border);
            color: #2563eb;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 4px 2px;
            text-align: center;
            text-decoration: none;
        }
        .pagination {
            margin-top: 16px;
        }
        .pagination nav[role="navigation"] {
            align-items: center;
            display: flex;
            justify-content: flex-end;
        }
        .pagination nav[role="navigation"] > div:first-child {
            display: none;
        }
        .pagination nav[role="navigation"] > div:last-child {
            align-items: center;
            display: flex;
            gap: 14px;
            justify-content: flex-end;
            width: 100%;
        }
        .pagination nav[role="navigation"] > div:last-child > div:first-child {
            color: #526381;
            font-size: 13px;
            font-weight: 600;
        }
        .pagination nav[role="navigation"] > div:last-child > div:last-child > span {
            align-items: center;
            display: inline-flex;
            gap: 5px;
        }
        .pagination nav[role="navigation"] > div:last-child > div:last-child a,
        .pagination nav[role="navigation"] > div:last-child > div:last-child span[aria-current] > span,
        .pagination nav[role="navigation"] > div:last-child > div:last-child span[aria-disabled] > span {
            align-items: center;
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            color: #334155;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            height: 36px;
            justify-content: center;
            min-width: 36px;
            padding: 0 10px;
            text-decoration: none;
        }
        .pagination nav[role="navigation"] > div:last-child > div:last-child a:hover {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #2563eb;
        }
        .pagination nav[role="navigation"] > div:last-child > div:last-child span[aria-current] > span {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        .pagination nav[role="navigation"] > div:last-child > div:last-child span[aria-disabled] > span {
            background: #f1f5f9;
            color: #94a3b8;
        }
        .pagination svg {
            height: 18px;
            width: 18px;
        }
        .visitor-edit-trigger {
            align-items: center;
            display: inline-flex;
            gap: 8px;
            white-space: nowrap;
        }
        .visitor-edit-trigger svg {
            height: 17px;
            width: 17px;
        }
        .visitor-modal-backdrop {
            align-items: center;
            background: rgba(15, 23, 42, .48);
            display: flex;
            inset: 0;
            justify-content: center;
            padding: 18px;
            position: fixed;
            z-index: 120;
        }
        .visitor-modal-backdrop[hidden] {
            display: none;
        }
        .visitor-modal {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
            max-width: 460px;
            overflow: hidden;
            width: 100%;
        }
        .visitor-modal-header {
            align-items: flex-start;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 14px;
            justify-content: space-between;
            padding: 18px;
        }
        .visitor-modal-header h2 {
            color: #0f172a;
            font-size: 20px;
            margin: 0;
        }
        .visitor-modal-header p {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            margin: 5px 0 0;
        }
        .visitor-modal-close {
            background: #f1f5f9;
            border: 0;
            border-radius: 8px;
            color: #334155;
            cursor: pointer;
            font-size: 24px;
            height: 34px;
            line-height: 1;
            width: 34px;
        }
        .visitor-modal-body {
            display: grid;
            gap: 14px;
            padding: 18px;
        }
        .visitor-modal-body label {
            display: grid;
            gap: 7px;
        }
        .visitor-modal-body label span {
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
        }
        .visitor-modal-body label strong {
            color: #ef4444;
        }
        .visitor-modal-body input {
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            color: #0f172a;
            font: inherit;
            min-height: 44px;
            padding: 10px 12px;
        }
        .visitor-modal-audit {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            padding: 10px 12px;
        }
        .visitor-modal-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #991b1b;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 12px;
        }
        .visitor-modal-actions {
            align-items: center;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 14px 18px;
        }
        .agent-user-chip {
            align-items: center;
            display: inline-flex;
            gap: 10px;
        }
        .agent-avatar {
            align-items: center;
            background: #fce7f3;
            border-radius: 999px;
            color: #db2777;
            display: inline-flex;
            font-size: 16px;
            font-weight: 800;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .agent-user-name {
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.1;
        }
        .agent-user-status {
            align-items: center;
            color: #475569;
            display: flex;
            font-size: 13px;
            gap: 6px;
            margin-top: 3px;
        }
        .agent-user-status::before {
            background: #22c55e;
            border-radius: 999px;
            content: "";
            height: 8px;
            width: 8px;
        }
        .agent-user-status.away::before { background: #f59e0b; }
        .agent-user-status.offline::before { background: #64748b; }
        h1 { font-size: 26px; margin: 0; }
        .muted { color: var(--muted); }
        .grid { display: grid; gap: 16px; }
        .grid > *,
        .panel,
        .card,
        .item,
        .panel-body,
        .panel-header { min-width: 0; }
        .cards { grid-template-columns: repeat(auto-fit, minmax(min(190px, 100%), 1fr)); }
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
            gap: 12px;
            justify-content: space-between;
            padding: 14px 16px;
        }
        .panel-header > strong { font-size: 20px; font-weight: 600; }
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
            display: grid;
            gap: 12px;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            padding: 12px;
            text-decoration: none;
        }
        .item-avatar {
            align-items: center;
            background: #f3e8ff;
            border-radius: 999px;
            color: #6d28d9;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 16px;
            font-weight: 800;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .item-actions {
            display: grid;
            flex: 0 0 auto;
            gap: 8px;
            justify-items: end;
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
        .item-last-message {
            align-items: center;
            display: flex;
            gap: 8px;
            max-width: 100%;
        }
        .item-last-message span {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .item-last-message time {
            color: #94a3b8;
            flex: 0 0 auto;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
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
        .agent-chat-status-badge {
            border-radius: 999px;
            display: inline-flex;
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
            padding: 5px 8px;
            white-space: nowrap;
        }
        .agent-chat-status-badge.active { background: #dcfce7; color: #166534; }
        .agent-chat-status-badge.on_hold { background: #fef3c7; color: #92400e; }
        .agent-chat-status-badge.awaiting_visitor { background: #dbeafe; color: #1d4ed8; }
        .agent-chat-status-control {
            align-items: center;
            background: #fff;
            border: 1px solid #86efac;
            border-radius: 12px;
            display: inline-flex;
            gap: 0;
            padding: 0;
            transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
        }
        .agent-chat-status-control.active { background: #fff; border-color: #4ade80; box-shadow: 0 0 0 3px rgba(74, 222, 128, .16); }
        .agent-chat-status-control.on_hold { background: #fffbeb; border-color: #facc15; box-shadow: 0 6px 16px rgba(146, 64, 14, .08); }
        .agent-chat-status-control.awaiting_visitor { background: #eff6ff; border-color: #93c5fd; box-shadow: 0 6px 16px rgba(29, 78, 216, .08); }
        .agent-chat-status-control label {
            align-items: center;
            align-self: stretch;
            border-right: 1px solid #dbe4ef;
            color: #64748b;
            display: inline-flex;
            font-size: 13px;
            font-weight: 650;
            padding: 0 12px;
        }
        .agent-chat-status-menu-wrap { position: relative; }
        .agent-chat-status-trigger {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            gap: 8px;
            justify-content: space-between;
            min-width: 132px;
            padding: 0 10px;
            transition: box-shadow .15s ease, transform .15s ease;
        }
        .agent-chat-status-dot {
            background: #22c55e;
            border-radius: 999px;
            flex: 0 0 auto;
            height: 9px;
            width: 9px;
        }
        .agent-chat-status-control.on_hold .agent-chat-status-dot { background: #f59e0b; }
        .agent-chat-status-control.awaiting_visitor .agent-chat-status-dot { background: #2563eb; }
        .agent-chat-status-trigger:hover { transform: translateY(-1px); }
        .agent-chat-status-trigger:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .18);
            outline: 0;
        }
        .agent-chat-status-trigger:disabled {
            cursor: wait;
            opacity: .65;
            transform: none;
        }
        .agent-chat-status-chevron {
            color: #475569;
            flex: 0 0 auto;
            font-size: 0;
            height: 20px;
            line-height: 1;
            margin-top: 0;
            overflow: hidden;
            position: relative;
            width: 20px;
        }
        .agent-chat-status-chevron::before {
            border-bottom: 2px solid currentColor;
            border-right: 2px solid currentColor;
            content: "";
            height: 8px;
            left: 5px;
            position: absolute;
            top: 4px;
            transform: rotate(45deg);
            width: 8px;
        }
        .agent-chat-status-control.active .agent-chat-status-trigger { color: #166534; }
        .agent-chat-status-control.on_hold .agent-chat-status-trigger { color: #92400e; }
        .agent-chat-status-control.awaiting_visitor .agent-chat-status-trigger { color: #1d4ed8; }
        .agent-chat-status-menu {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .15);
            display: grid;
            gap: 6px;
            left: 0;
            margin-top: 8px;
            min-width: 260px;
            padding: 8px;
            position: absolute;
            top: 100%;
            z-index: 40;
        }
        .agent-chat-status-menu[hidden] { display: none; }
        .agent-chat-status-option {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 10px;
            color: #334155;
            cursor: pointer;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 12px 14px;
            text-align: left;
            width: 100%;
        }
        .agent-chat-status-option:hover,
        .agent-chat-status-option[aria-selected="true"] { background: #f0fdf4; }
        .agent-chat-status-option-text {
            display: grid;
            gap: 3px;
            min-width: 0;
        }
        .agent-chat-status-option-text strong {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }
        .agent-chat-status-option-text small {
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.25;
        }
        .agent-chat-status-check {
            display: none;
            flex: 0 0 auto;
            height: 20px;
            margin-top: 2px;
            width: 20px;
        }
        .agent-chat-status-option[aria-selected="true"] .agent-chat-status-check { display: block; }
        .agent-chat-status-option.active { color: #166534; }
        .agent-chat-status-option.on_hold { color: #92400e; }
        .agent-chat-status-option.awaiting_visitor { color: #1d4ed8; }
        .agent-chat-status-error {
            color: var(--danger);
            font-size: 12px;
            font-weight: 700;
            margin-top: 4px;
        }
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
            flex-shrink: 0;
            margin-bottom: 16px;
            padding: 12px 14px;
        }
        .notice { background: #dcfce7; color: #166534; }
        .errors { background: #fee2e2; color: #991b1b; }
        .agent-live-toast {
            align-items: center;
            background: #111827;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 999px;
            bottom: 24px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .24);
            color: #fff;
            cursor: pointer;
            display: flex;
            font-size: 14px;
            font-weight: 800;
            gap: 10px;
            line-height: 1.25;
            max-width: min(320px, calc(100vw - 32px));
            padding: 12px 16px;
            position: fixed;
            right: 24px;
            z-index: 90;
        }
        .agent-live-toast[hidden] { display: none; }
        .agent-live-toast strong { font-weight: 600; }
        .agent-live-toast span {
            background: #ef4444;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            justify-content: center;
            min-width: 22px;
            padding: 4px 7px;
        }
        .chat-layout {
            display: grid;
            flex: 1;
            gap: clamp(12px, 1vw, 16px);
            grid-template-columns: clamp(270px, 22vw, 330px) minmax(0, 1fr);
            min-height: 0;
            overflow: hidden;
        }
        .conversation-list {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }
        .conversation-list .panel-header {
            flex-shrink: 0;
        }
        .conversation-list > [data-realtime-refresh] {
            min-height: 0;
            overflow-y: auto;
        }
        .conversation-list > [data-realtime-refresh="chat-waiting-list"] {
            flex: 0 1 auto;
            max-height: min(38vh, 320px);
        }
        .conversation-list > [data-realtime-refresh="chat-active-list"] {
            flex: 1 1 auto;
        }
        .conversation-section-header { border-top: 1px solid var(--border); }
        .conversation-link {
            align-items: center;
            border-bottom: 1px solid var(--border);
            display: grid;
            gap: 10px;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            padding: 12px;
            text-decoration: none;
            transition: background-color .16s ease, border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }
        .conversation-avatar {
            align-items: center;
            align-self: start;
            background: #f3e8ff;
            border-radius: 999px;
            color: #4f46e5;
            display: inline-flex;
            font-size: 16px;
            font-weight: 800;
            height: 38px;
            justify-content: center;
            width: 38px;
        }
        .conversation-link-main {
            min-width: 0;
        }
        .conversation-link .item-title {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .conversation-last-message {
            color: #172033;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.25;
            margin-top: 3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .conversation-link-meta {
            align-items: end;
            align-self: stretch;
            display: flex;
            flex-direction: column;
            gap: 7px;
            justify-content: center;
            min-width: 62px;
        }
        .conversation-link-meta time {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }
        .conversation-link.active {
            background: #1e3a5f;
            border-left: 3px solid #2563eb;
        }
        .conversation-link:hover {
            background: #f8fbff;
            box-shadow: inset 3px 0 0 #93c5fd;
            transform: translateX(2px);
        }
        .conversation-link.active:hover {
            background: #1e3a5f;
            box-shadow: inset 3px 0 0 #60a5fa;
        }
        .conversation-link.active:hover .item-title,
        .conversation-link.active:hover .conversation-last-message { color: #fff; }
        .conversation-link.active:hover .item-sub,
        .conversation-link.active:hover .conversation-link-meta time { color: #cbd5e1; }
        .conversation-link.active .item-title,
        .conversation-link.active .conversation-last-message { color: #fff; }
        .conversation-link.active .item-sub,
        .conversation-link.active .conversation-link-meta time { color: #cbd5e1; }
        .conversation-link:hover .item-title { color: var(--text); }
        .conversation-link:hover .item-sub { color: var(--muted); }
        .conversation-link:hover .conversation-last-message { color: #172033; }
        .conversation-link:hover .conversation-link-meta time { color: #475569; }
        .conversation-link.active .agent-chat-status-badge.on_hold {
            background: rgba(251, 191, 36, .18);
            color: #fde68a;
        }
        .conversation-link.active .agent-chat-status-badge.active {
            background: rgba(34, 197, 94, .18);
            color: #bbf7d0;
        }
        .conversation-link.active .agent-chat-status-badge.awaiting_visitor {
            background: rgba(96, 165, 250, .2);
            color: #bfdbfe;
        }
        .chat-panel {
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            position: relative;
        }
        .closed-conversation-layout {
            display: grid;
            gap: 16px;
            grid-template-columns: minmax(0, 1fr) clamp(320px, 27vw, 420px);
            margin-top: 54px;
            min-height: 0;
            overflow: hidden;
        }
        .closed-main-column {
            display: grid;
            gap: 16px;
            min-height: 0;
            min-width: 0;
        }
        .closed-conversation-layout .chat-panel {
            min-width: 0;
        }
        .closed-conversation-layout .chat-conversation-header {
            align-items: center;
            border-bottom: 1px solid var(--border);
            gap: 16px;
            padding: 18px;
        }
        .closed-chat-heading {
            align-items: center;
            display: flex;
            gap: 18px;
            min-width: 0;
        }
        .closed-back-btn {
            align-items: center;
            background: #eaf3ff;
            border-radius: 8px;
            color: #2563eb;
            display: inline-flex;
            flex: 0 0 auto;
            font-weight: 600;
            gap: 8px;
            height: 44px;
            padding: 0 14px;
        }
        .closed-back-btn svg,
        .closed-download-btn svg,
        .closed-lock svg {
            height: 18px;
            width: 18px;
        }
        .closed-title-block {
            color: #64748b;
            font-size: 14px;
            min-width: 0;
        }
        .closed-title-line {
            align-items: center;
            color: #0f172a;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 5px;
        }
        .closed-title-line strong {
            font-size: 24px;
            font-weight: 600;
        }
        .closed-lock {
            color: #e11d48;
            display: inline-flex;
        }
        .closed-state-badge {
            background: #ffe4e6;
            border-radius: 8px;
            color: #e11d48;
            font-size: 14px;
            font-weight: 600;
            padding: 7px 12px;
        }
        .closed-download-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
            margin-left: auto;
        }
        .closed-download-btn {
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #0f172a;
            cursor: pointer;
            display: inline-flex;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            gap: 8px;
            height: 44px;
            padding: 0 14px;
            text-decoration: none;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
            white-space: nowrap;
        }
        .closed-download-btn:hover {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
            transform: translateY(-1px);
        }
        .closed-download-btn:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
        .closed-detail-panel {
            align-self: start;
            max-height: 100%;
            overflow: auto;
        }
        .closed-detail-panel .panel-header {
            justify-content: flex-start;
        }
        .closed-section-title {
            align-items: center;
            display: inline-flex;
            gap: 10px;
        }
        .closed-section-title svg {
            color: #2563eb;
            height: 21px;
            width: 21px;
        }
        .closed-detail-list {
            display: grid;
            gap: 8px;
        }
        .closed-detail-row {
            align-items: center;
            display: grid;
            gap: 12px;
            grid-template-columns: 112px 32px minmax(0, 1fr);
        }
        .closed-detail-row span {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
        }
        .closed-detail-icon {
            align-items: center;
            background: #eff6ff;
            border-radius: 999px;
            color: #2563eb;
            display: inline-flex;
            height: 32px;
            justify-content: center;
            width: 32px;
        }
        .closed-detail-icon svg {
            height: 18px;
            width: 18px;
        }
        .closed-detail-row strong {
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
        .closed-note-header {
            border-top: 1px solid var(--border);
        }
        .closed-note-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #475569;
            min-height: 86px;
            padding: 14px;
            white-space: pre-wrap;
        }
        .closed-summary-panel .panel-header {
            justify-content: flex-start;
        }
        .closed-summary-title {
            align-items: center;
            display: inline-flex;
            gap: 8px;
        }
        .closed-summary-title svg {
            color: #2563eb;
            flex: 0 0 auto;
            height: 16px;
            width: 16px;
        }
        .closed-summary-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(min(180px, 100%), 1fr));
            max-width: 100%;
        }
        .closed-summary-card {
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            gap: 10px;
            min-height: 78px;
            padding: 12px;
        }
        .closed-summary-card > div {
            align-items: center;
            display: grid;
            flex: 1 1 auto;
            gap: 8px;
            grid-template-columns: minmax(0, 1fr) auto;
            min-width: 0;
        }
        .closed-summary-icon {
            align-items: center;
            background: #dbeafe;
            border-radius: 999px;
            color: #2563eb;
            display: inline-flex;
            flex: 0 0 auto;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .closed-summary-icon svg {
            height: 22px;
            width: 22px;
        }
        .closed-summary-card div span {
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.3;
            min-width: 0;
            overflow-wrap: normal;
            word-break: normal;
        }
        .closed-summary-card strong {
            color: #0f172a;
            font-size: 24px;
            font-weight: 600;
            line-height: 1;
        }
        .closed-conversation-layout .closed-conversation-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            margin: 0;
            padding: 9px 12px;
        }
        .chat-conversation-header {
            align-items: center;
            gap: 16px;
            padding: 16px 18px;
        }
        .chat-conversation-info {
            align-items: center;
            display: grid;
            gap: 12px;
            grid-template-columns: 44px minmax(0, 1fr);
            min-width: 0;
        }
        .chat-conversation-avatar {
            align-items: center;
            background: #f3e8ff;
            border-radius: 999px;
            color: #6d28d9;
            display: inline-flex;
            font-size: 17px;
            font-weight: 800;
            height: 44px;
            justify-content: center;
            width: 44px;
        }
        .chat-conversation-copy {
            min-width: 0;
        }
        .chat-conversation-title {
            color: #0f172a;
            display: block;
            font-size: 17px;
            font-weight: 600;
            line-height: 1.25;
        }
        .chat-conversation-meta {
            align-items: center;
            color: var(--muted);
            display: flex;
            flex-wrap: wrap;
            font-size: 14px;
            gap: 8px;
            margin-top: 6px;
        }
        .chat-conversation-meta .status-pill {
            margin-left: 0;
        }
        .chat-header-actions {
            align-items: center;
            display: flex;
            flex: 1 1 auto;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
            margin-left: auto;
            min-width: min(100%, 300px);
        }
        .chat-header-actions .btn,
        .chat-header-actions button.btn {
            align-items: center;
            font-size: 14px;
            font-weight: 600;
            height: 44px;
            padding: 0 16px;
            white-space: nowrap;
        }
        .chat-header-actions form:not(.agent-chat-status-control) {
            display: flex;
            margin: 0;
        }
        .chat-header-actions .agent-chat-status-control {
            align-items: center;
            border-radius: 10px;
            display: inline-flex;
            gap: 0;
            height: 42px;
            margin: 0;
            padding: 0;
            width: min(210px, 100%);
        }
        .chat-header-actions .agent-chat-status-control label {
            flex: 0 0 auto;
            font-size: 13px;
            font-weight: 600;
        }
        .chat-header-actions .agent-chat-status-menu-wrap {
            flex: 1 1 auto;
            min-width: 0;
        }
        .chat-header-actions .agent-chat-status-trigger {
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            height: 100%;
            min-width: 0;
            padding: 0 10px;
            width: 100%;
        }
        .chat-header-actions .agent-chat-status-trigger:hover {
            transform: none;
        }
        .chat-header-actions .agent-chat-status-current {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .chat-header-actions .agent-chat-status-chevron {
            height: 20px;
            line-height: 1;
            margin-top: 0;
            width: 20px;
        }
        .chat-header-actions .agent-chat-status-menu {
            min-width: 280px;
            right: 0;
            left: auto;
        }
        .messages {
            align-content: start;
            display: grid;
            gap: 12px;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            padding: 18px;
        }
        .chat-new-messages {
            align-items: center;
            background: #2563eb;
            border: 0;
            border-radius: 999px;
            bottom: 112px;
            box-shadow: 0 16px 34px rgba(37, 99, 235, .24);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 6px;
            left: 50%;
            padding: 9px 14px;
            position: absolute;
            transform: translateX(-50%);
            z-index: 5;
        }
        .chat-new-messages[hidden] { display: none; }
        .conversation-unread-count {
            align-items: center;
            background: #2563eb;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            height: 20px;
            justify-content: center;
            min-width: 20px;
            padding: 0 6px;
        }
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
            max-width: min(76%, 760px);
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
        .voice-player {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 999px;
            display: grid;
            gap: 8px;
            grid-template-columns: 30px minmax(110px, 1fr) auto;
            max-width: 280px;
            min-width: 200px;
            padding: 7px 9px;
            width: min(260px, 100%);
        }
        .voice-audio { display: none; }
        .voice-play {
            align-items: center;
            background: var(--primary);
            border: 0;
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            height: 30px;
            justify-content: center;
            padding: 0;
            width: 30px;
        }
        .voice-track {
            background: #e5e7eb;
            border: 0;
            border-radius: 999px;
            cursor: pointer;
            height: 28px;
            overflow: hidden;
            padding: 0;
            position: relative;
        }
        .voice-progress {
            background: #bfdbfe;
            bottom: 0;
            left: 0;
            position: absolute;
            top: 0;
            width: 0;
        }
        .voice-bars {
            align-items: center;
            bottom: 0;
            display: flex;
            gap: 3px;
            left: 8px;
            position: absolute;
            right: 8px;
            top: 0;
        }
        .voice-bars span {
            background: #6b7280;
            border-radius: 999px;
            flex: 1;
            height: var(--bar-height);
            min-width: 2px;
        }
        .voice-time {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
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
        .composer {
            border-top: 1px solid var(--border);
            flex-shrink: 0;
            padding: 14px;
        }
        .agent-attachment-row {
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--muted);
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 10px;
            padding: 8px 10px;
        }
        .agent-attachment-row[hidden] { display: none; }
        .agent-attachment-row span {
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .closed-conversation-message {
            align-items: center;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #991b1b;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.3;
            padding: 8px 10px;
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
        .composer-actions {
            flex-wrap: wrap;
            gap: 8px;
        }
        .composer-actions .btn {
            align-items: center;
            gap: 7px;
        }
        .composer-actions .btn svg {
            flex: 0 0 auto;
            height: 16px;
            width: 16px;
        }
        .agent-recording-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }
        .agent-recording-row[hidden] { display: none; }
        .agent-recording-row span {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 999px;
            color: #991b1b;
            font-size: 14px;
            font-weight: 700;
            padding: 8px 12px;
        }
        .profile-grid {
            align-items: start;
            display: grid;
            gap: 16px;
            grid-template-areas:
                "activity activity"
                "summary info"
                "calendar calendar";
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
        }
        .profile-activity-panel { grid-area: activity; }
        .profile-hero {
            grid-area: summary;
            overflow: hidden;
            padding: 26px 20px;
            text-align: center;
        }
        .profile-info-panel { grid-area: info; }
        .profile-calendar-panel { grid-area: calendar; }
        .profile-calendar-panel > .panel-header {
            padding: 18px 20px;
        }
        .profile-calendar-panel .panel-body {
            max-width: 1440px;
        }
        .profile-calendar-heading {
            align-items: center;
            display: flex;
            gap: 14px;
        }
        .profile-calendar-heading-icon {
            align-items: center;
            background: #dbeafe;
            border-radius: 12px;
            color: #2563eb;
            display: inline-flex;
            flex: 0 0 auto;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .profile-calendar-heading-icon svg {
            height: 26px;
            width: 26px;
        }
        .profile-calendar-heading strong {
            color: #111827;
            display: block;
            font-size: 19px;
            font-weight: 600;
            line-height: 1.1;
        }
        .profile-calendar-heading small {
            color: #64748b;
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-top: 5px;
        }
        .profile-calendar-nav {
            align-items: center;
            display: flex;
            gap: 10px;
        }
        .profile-calendar-nav .profile-muted {
            color: #000;
            font-size: 17px;
            font-weight: 600;
        }
        .profile-calendar-nav-button,
        .profile-calendar-today-button {
            align-items: center;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #0775be;
            display: inline-flex;
            font-size: 13px;
            font-weight: 800;
            height: 44px;
            justify-content: center;
            min-width: 44px;
            padding: 0 14px;
        }
        .profile-calendar-today-button {
            background: #eff6ff;
            border-color: #dbeafe;
            color: #2563eb;
            font-size: 15px;
        }
        .profile-calendar-nav-button svg {
            height: 18px;
            width: 18px;
        }
        .profile-calendar-nav-button:hover,
        .profile-calendar-today-button:hover {
            border-color: #93c5fd;
            color: var(--primary);
        }
        .profile-summary { margin-top: 0; }
        .profile-summary h2 { font-size: 26px; margin: 0; }
        .profile-role {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 999px;
            color: #9a3412;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            margin-top: 8px;
            padding: 5px 10px;
        }
        .availability-pill {
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            display: inline-flex;
            font-size: 14px;
            font-weight: 800;
            gap: 8px;
            margin-top: 14px;
            padding: 9px 16px;
        }
        .availability-pill span {
            border-radius: 999px;
            height: 9px;
            width: 9px;
        }
        .availability-pill.online { background: #f0fdf4; border-color: #86efac; color: #166534; }
        .availability-pill.online span { background: #22c55e; }
        .availability-pill.away { background: #fffbeb; border-color: #facc15; color: #92400e; }
        .availability-pill.away span { background: #f59e0b; }
        .availability-pill.offline { background: #f8fafc; color: #475569; }
        .availability-pill.offline span { background: #64748b; }
        .profile-detail-list {
            border-top: 1px solid var(--border);
            display: grid;
            gap: 0;
            margin-top: 20px;
            padding-top: 10px;
            text-align: left;
        }
        .profile-detail-row {
            align-items: center;
            display: flex;
            gap: 14px;
            justify-content: space-between;
            padding: 11px 0;
        }
        .profile-detail-label,
        .profile-field-shell,
        .profile-stat-shell {
            align-items: center;
            display: flex;
            gap: 10px;
            min-width: 0;
        }
        .profile-detail-icon,
        .profile-field-icon,
        .profile-stat-icon {
            align-items: center;
            border-radius: 8px;
            display: inline-flex;
            flex: 0 0 auto;
            height: 34px;
            justify-content: center;
            width: 34px;
        }
        .profile-detail-icon svg,
        .profile-field-icon svg,
        .profile-stat-icon svg {
            height: 18px;
            width: 18px;
        }
        .profile-detail-icon { background: #f8fafc; color: #2563eb; }
        .profile-field-icon { background: #eff6ff; color: #2563eb; }
        .profile-stat-icon.chat { background: #eff6ff; color: #2563eb; }
        .profile-stat-icon.rating { background: #fff7ed; color: #f59e0b; }
        .profile-detail-row span { color: var(--muted); font-size: 14px; font-weight: 600; }
        .profile-detail-row strong { color: var(--text); font-size: 15px; text-align: right; }
        .profile-info-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .profile-stat-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 18px;
        }
        .profile-activity-cards {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .profile-activity-card {
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            gap: 14px;
            padding: 16px;
            min-width: 0;
        }
        .profile-activity-card.login { background: #eff6ff; border-color: #bfdbfe; }
        .profile-activity-card.login-time { background: #f0fdf4; border-color: #bbf7d0; }
        .profile-activity-card.break { background: #fffbeb; border-color: #fde68a; }
        .profile-activity-card.handled { background: #fff1f2; border-color: #fecdd3; }
        .profile-activity-card.rating { background: #fff7ed; border-color: #fed7aa; }
        .profile-activity-icon {
            align-items: center;
            border-radius: 8px;
            display: inline-flex;
            flex: 0 0 auto;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .profile-activity-icon svg { height: 22px; width: 22px; }
        .profile-activity-card.login .profile-activity-icon { background: #dbeafe; color: #2563eb; }
        .profile-activity-card.login-time .profile-activity-icon { background: #dcfce7; color: #16a34a; }
        .profile-activity-card.break .profile-activity-icon { background: #fef3c7; color: #f59e0b; }
        .profile-activity-card.handled .profile-activity-icon { background: #ffe4e6; color: #e11d48; }
        .profile-activity-card.rating .profile-activity-icon { background: #ffedd5; color: #f59e0b; }
        .profile-activity-value {
            color: var(--text);
            font-size: clamp(22px, 1.8vw, 30px);
            font-weight: 600;
            margin-top: 8px;
            overflow-wrap: anywhere;
        }
        .profile-field,
        .profile-stat {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            min-height: 74px;
            padding: 14px 12px;
        }
        .profile-stat-shell {
            align-items: center;
            gap: 12px;
            justify-content: center;
        }
        .profile-stat-shell > div {
            flex: 1 1 auto;
            min-width: 0;
            text-align: center;
        }
        .profile-field label,
        .profile-field-title,
        .profile-stat-title {
            color: var(--muted);
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .profile-stat .profile-stat-title {
            align-items: flex-end;
            display: flex;
            justify-content: center;
            line-height: 1.25;
            min-height: 32px;
            white-space: normal;
        }
        .profile-field input {
            border: 1px solid var(--border);
            border-radius: 8px;
            font-weight: 700;
            padding: 10px 12px;
            width: 100%;
        }
        .profile-value,
        .profile-stat-value {
            color: var(--text);
            font-size: 16px;
            font-weight: 600;
            word-break: break-word;
        }
        .profile-stat-value {
            line-height: 1.2;
            text-align: center;
        }
        .profile-muted { color: var(--muted); font-size: 13px; }
        .status-value {
            align-items: center;
            display: flex;
            gap: 4px;
        }
        .website-chip-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .website-chip {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 999px;
            color: #3730a3;
            font-size: 12px;
            font-weight: 800;
            padding: 6px 9px;
        }
        .profile-calendar-layout {
            align-items: start;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 380px);
        }
        .profile-calendar {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
        .profile-calendar-weekday {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-align: center;
        }
        .profile-calendar-empty,
        .profile-calendar-day {
            border-radius: 8px;
            min-height: 92px;
        }
        .profile-calendar-day {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
            cursor: pointer;
            display: grid;
            font: inherit;
            gap: 5px;
            padding: 12px;
            text-align: left;
        }
        .profile-calendar-day:hover,
        .profile-calendar-day.selected {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }
        .profile-calendar-day.today { background: #eff6ff; border-color: #93c5fd; }
        .profile-calendar-day span { font-size: 18px; font-weight: 600; }
        .profile-calendar-day small {
            align-self: end;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }
        .profile-calendar-detail {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
        }
        .profile-calendar-detail-title {
            align-items: center;
            color: var(--text);
            display: flex;
            font-size: 17px;
            font-weight: 600;
            gap: 12px;
            margin-bottom: 8px;
        }
        .profile-calendar-detail-icon {
            align-items: center;
            background: #dbeafe;
            border-radius: 12px;
            color: #2563eb;
            display: inline-flex;
            flex: 0 0 auto;
            height: 46px;
            justify-content: center;
            width: 46px;
        }
        .profile-calendar-detail-icon svg {
            height: 24px;
            width: 24px;
        }
        .profile-calendar-detail-date {
            color: #2563eb;
            font-size: 15px;
            font-weight: 600;
            margin: -18px 0 16px 58px;
        }
        .profile-summary-row {
            align-items: center;
            border-top: 1px solid #edf2f7;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 12px 0;
        }
        .profile-summary-label {
            align-items: center;
            color: var(--muted);
            display: inline-flex;
            font-size: 14px;
            font-weight: 600;
            gap: 12px;
            min-width: 0;
        }
        .profile-summary-label svg {
            border-radius: 10px;
            box-sizing: content-box;
            flex: 0 0 auto;
            height: 20px;
            padding: 9px;
            width: 20px;
        }
        .profile-summary-label.chat svg { background: #dcfce7; color: #16a34a; }
        .profile-summary-label.missed svg { background: #fef3c7; color: #f59e0b; }
        .profile-summary-label.online svg { background: #dbeafe; color: #2563eb; }
        .profile-summary-label.break svg { background: #ffedd5; color: #f97316; }
        .profile-summary-label.login svg { background: #dcfce7; color: #22c55e; }
        .profile-summary-label.logout svg { background: #fee2e2; color: #ef4444; }
        .profile-summary-row strong {
            color: var(--text);
            font-size: 14px;
            font-weight: 600;
            text-align: right;
            white-space: nowrap;
        }
        .pagination { margin-top: 16px; }
        @media (min-width: 1200px) and (max-width: 1500px) {
            .content {
                padding: 16px 20px 22px;
            }
            .nav a,
            .nav button {
                font-size: 14px;
                padding: 9px 10px;
            }
            .card { padding: 15px; }
            .card .value { font-size: 28px; }
            .panel-header { padding: 13px 14px; }
            .panel-header > strong { font-size: 18px; }
            .chat-layout {
                grid-template-columns: clamp(230px, 18vw, 260px) minmax(0, 1fr);
            }
            .chat-conversation-header {
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .chat-header-actions {
                justify-content: flex-start;
                margin-left: 0;
                width: 100%;
            }
            .closed-conversation-layout {
                grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
            }
            .closed-detail-row {
                gap: 10px;
                grid-template-columns: 88px 32px minmax(0, 1fr);
            }
            .closed-detail-icon {
                height: 32px;
                width: 32px;
            }
            .closed-summary-grid {
                grid-template-columns: repeat(auto-fit, minmax(min(160px, 100%), 1fr));
            }
            .profile-activity-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .profile-calendar-layout {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 900px) {
            body {
                overflow: auto;
            }
            .shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }
            .sidebar {
                height: auto;
                overflow: visible;
                position: static;
            }
            .content {
                height: auto;
                min-height: 0;
                overflow: visible;
                padding: 16px;
            }
            .cards, .chat-layout, .profile-grid, .profile-calendar-layout { grid-template-columns: 1fr; }
            .chat-layout {
                height: auto;
                overflow: visible;
            }
            .conversation-list {
                display: block;
                max-height: calc(100vh - 170px);
                overflow: auto;
            }
            .conversation-list > [data-realtime-refresh] {
                max-height: none;
                overflow: visible;
            }
            .chat-panel {
                height: auto;
                min-height: calc(100vh - 120px);
                overflow: visible;
            }
            .closed-conversation-layout {
                grid-template-columns: 1fr;
                height: auto;
                margin-top: 0;
                overflow: visible;
            }
            .closed-conversation-layout .chat-conversation-header,
            .closed-chat-heading,
            .closed-download-actions {
                align-items: flex-start;
                flex-direction: column;
                justify-content: flex-start;
            }
            .closed-download-actions {
                margin-left: 0;
                width: 100%;
            }
            .closed-download-btn {
                justify-content: center;
                width: 100%;
            }
            .closed-detail-panel {
                max-height: none;
                order: 2;
            }
            .chat-conversation-header {
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .chat-header-actions {
                justify-content: flex-start;
                margin-left: 0;
                width: 100%;
            }
            .messages { min-height: 360px; }
            .profile-grid {
                grid-template-areas:
                    "activity"
                    "summary"
                    "info"
                    "calendar";
            }
            .profile-info-grid { grid-template-columns: 1fr; }
            .profile-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .profile-activity-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .chat-conversation-header {
                padding: 14px;
            }
            .chat-conversation-meta {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }
            .chat-header-actions {
                display: grid;
                grid-template-columns: 1fr;
            }
            .closed-detail-row {
                grid-template-columns: 36px minmax(0, 1fr);
                gap: 8px 10px;
            }
            .closed-detail-row > span:first-child {
                grid-column: 1 / -1;
            }
            .chat-header-actions .agent-chat-status-control,
            .chat-header-actions .btn,
            .chat-header-actions button.btn,
            .chat-header-actions form:not(.agent-chat-status-control) {
                width: 100%;
            }
            .profile-activity-cards,
            .profile-stat-grid { grid-template-columns: 1fr; }
            .profile-calendar { gap: 5px; }
            .profile-calendar-empty,
            .profile-calendar-day { min-height: 54px; }
            .profile-calendar-day { padding: 7px; }
            .profile-calendar-day small { display: none; }
        }
        @media (min-width: 1440px) {
            .content {
                padding-left: 28px;
                padding-right: 28px;
            }
            .profile-calendar-panel .panel-body {
                max-width: none;
            }
        }
        @media (min-width: 1200px) and (max-width: 1439px) {
            .closed-conversation-layout {
                grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
            }
            .profile-calendar-layout {
                grid-template-columns: 1fr;
            }
        }
        @media (min-width: 992px) and (max-width: 1199px) {
            .shell {
                grid-template-columns: 220px minmax(0, 1fr);
            }
            .sidebar {
                padding-left: 12px;
                padding-right: 12px;
            }
            .content {
                padding: 14px 18px 22px;
            }
            .chat-layout {
                grid-template-columns: 240px minmax(0, 1fr);
            }
            .closed-conversation-layout,
            .profile-calendar-layout {
                grid-template-columns: 1fr;
                margin-top: 0;
                overflow: visible;
            }
            .closed-detail-panel {
                max-height: none;
            }
            .profile-activity-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .profile-grid {
                grid-template-columns: minmax(260px, 320px) minmax(0, 1fr);
            }
            .profile-info-grid {
                grid-template-columns: 1fr;
            }
            .bubble {
                max-width: 84%;
            }
        }
        @media (min-width: 768px) and (max-width: 991px) {
            body {
                overflow: auto;
            }
            .shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }
            .sidebar {
                height: auto;
                max-height: none;
                overflow: visible;
                padding: 14px 16px;
                position: static;
            }
            .brand {
                margin-bottom: 12px;
            }
            .nav {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            .nav a,
            .nav button {
                min-height: 44px;
            }
            .availability-box {
                display: grid;
                grid-template-columns: auto minmax(180px, 260px);
                align-items: center;
                column-gap: 12px;
            }
            .availability-box label {
                margin-bottom: 0;
            }
            .content {
                height: auto;
                min-height: 0;
                overflow: visible;
                padding: 16px;
            }
            .topbar.compact {
                position: static;
                margin-bottom: 14px;
            }
            .topbar-actions {
                flex-wrap: wrap;
            }
            .chat-layout,
            .closed-conversation-layout,
            .profile-grid,
            .profile-calendar-layout {
                grid-template-columns: 1fr;
                height: auto;
                margin-top: 0;
                overflow: visible;
            }
            .conversation-list {
                display: block;
                max-height: 320px;
                overflow: auto;
            }
            .chat-panel {
                height: auto;
                min-height: 600px;
                overflow: visible;
            }
            .messages {
                min-height: 380px;
            }
            .bubble {
                max-width: 86%;
            }
            .profile-activity-cards,
            .profile-info-grid,
            .profile-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .profile-grid {
                grid-template-areas:
                    "activity"
                    "summary"
                    "info"
                    "calendar";
            }
        }
        @media (min-width: 576px) and (max-width: 767px) {
            body {
                overflow: auto;
            }
            .shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }
            .sidebar {
                height: auto;
                overflow: visible;
                padding: 14px;
                position: static;
            }
            .brand {
                margin-bottom: 12px;
            }
            .nav {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .nav a,
            .nav button {
                min-height: 44px;
            }
            .availability-box {
                margin-top: 10px;
            }
            .content {
                height: auto;
                overflow: visible;
                padding: 14px;
            }
            .topbar,
            .topbar-actions {
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .topbar.compact {
                position: static;
                margin-bottom: 14px;
            }
            .agent-user-menu {
                min-width: 0;
            }
            .chat-layout,
            .closed-conversation-layout,
            .profile-grid,
            .profile-calendar-layout {
                grid-template-columns: 1fr;
                height: auto;
                margin-top: 0;
                overflow: visible;
            }
            .conversation-list {
                display: block;
                max-height: 300px;
                overflow: auto;
            }
            .chat-panel {
                height: auto;
                min-height: 560px;
                overflow: visible;
            }
            .messages {
                min-height: 340px;
                padding: 14px;
            }
            .bubble {
                max-width: 90%;
            }
            .profile-activity-cards,
            .profile-info-grid,
            .profile-stat-grid {
                grid-template-columns: 1fr;
            }
            .closed-detail-row {
                grid-template-columns: 36px minmax(0, 1fr);
                gap: 8px 10px;
            }
            .closed-detail-row > span:first-child {
                grid-column: 1 / -1;
            }
            .composer-actions,
            .logout-modal-actions {
                flex-wrap: wrap;
            }
        }
        @media (max-width: 575px) {
            body {
                overflow: auto;
            }
            .shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }
            .sidebar {
                height: auto;
                overflow: visible;
                padding: 12px;
                position: static;
            }
            .brand {
                margin-bottom: 12px;
            }
            .nav {
                grid-template-columns: 1fr;
            }
            .nav a,
            .nav button {
                min-height: 44px;
            }
            .content {
                height: auto;
                overflow: visible;
                padding: 12px;
            }
            .topbar,
            .topbar-actions {
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 10px;
                width: 100%;
            }
            .topbar.compact {
                position: static;
                margin-bottom: 12px;
            }
            .agent-notification-button,
            .agent-avatar {
                height: 36px;
                width: 36px;
            }
            .agent-user-menu {
                min-width: 0;
            }
            .agent-user-name,
            .agent-user-status {
                max-width: 160px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .card,
            .panel-body {
                padding: 12px;
            }
            .panel-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
                padding: 12px;
            }
            .item {
                align-items: stretch;
                grid-template-columns: 38px minmax(0, 1fr);
            }
            .item-avatar {
                height: 38px;
                width: 38px;
            }
            .item-actions {
                align-items: stretch;
                display: flex;
                flex-wrap: wrap;
                grid-column: 2;
                justify-content: flex-start;
            }
            .item-actions form,
            .item-actions .btn {
                flex: 1 1 auto;
            }
            .chat-layout,
            .closed-conversation-layout,
            .profile-grid,
            .profile-calendar-layout {
                grid-template-columns: 1fr;
                height: auto;
                margin-top: 0;
                overflow: visible;
            }
            .conversation-list {
                display: block;
                max-height: 280px;
                overflow: auto;
            }
            .chat-panel {
                height: auto;
                min-height: 520px;
                overflow: visible;
            }
            .messages {
                min-height: 320px;
                padding: 12px;
            }
            .bubble {
                max-width: 94%;
            }
            .message-attachment img,
            .message-attachment video,
            .attachment-card,
            .voice-player {
                max-width: 100%;
            }
            .voice-player {
                min-width: 0;
                grid-template-columns: 30px minmax(0, 1fr) auto;
            }
            .composer-actions,
            .canned-replies,
            .agent-recording-row,
            .agent-attachment-row,
            .logout-modal-actions {
                align-items: stretch;
                flex-direction: column;
            }
            .composer-actions .btn,
            .canned-replies .btn,
            .agent-recording-row .btn,
            .agent-attachment-row .btn,
            .logout-modal-actions .btn {
                width: 100%;
            }
            .profile-activity-cards,
            .profile-info-grid,
            .profile-stat-grid {
                grid-template-columns: 1fr;
            }
            .closed-detail-row,
            .profile-detail-row,
            .profile-summary-row {
                align-items: flex-start;
            }
            .profile-detail-row,
            .profile-summary-row {
                flex-direction: column;
            }
            .profile-detail-row strong,
            .profile-summary-row strong {
                text-align: left;
                white-space: normal;
            }
            .profile-calendar-panel > .panel-header {
                padding: 12px;
            }
            .profile-calendar-heading {
                align-items: flex-start;
            }
            .profile-calendar-nav {
                flex-wrap: wrap;
                width: 100%;
            }
            .profile-calendar-nav .profile-muted {
                flex: 1 1 100%;
            }
            .profile-calendar {
                gap: 4px;
            }
            .profile-calendar-weekday {
                font-size: 11px;
            }
            .profile-calendar-detail-date {
                margin: 0 0 12px;
            }
        }
        .agent-sidebar-toggle,
        .agent-sidebar-backdrop,
        .chat-list-toggle,
        .chat-list-backdrop {
            display: none;
        }
        @media (max-width: 1199px) {
            body {
                overflow: auto;
            }
            .agent-sidebar-toggle {
                align-items: center;
                background: #fff;
                border: 1px solid var(--border);
                border-radius: 8px;
                color: #0f172a;
                display: inline-flex;
                height: 42px;
                justify-content: center;
                left: 14px;
                position: fixed;
                top: 12px;
                width: 42px;
                z-index: 70;
            }
            .agent-sidebar-toggle svg {
                height: 21px;
                width: 21px;
            }
            .agent-sidebar-backdrop {
                background: rgba(15, 23, 42, .45);
                inset: 0;
                position: fixed;
                z-index: 58;
            }
            .agent-sidebar-backdrop.open {
                display: block;
            }
            .shell {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }
            .sidebar {
                bottom: 0;
                box-shadow: 18px 0 42px rgba(15, 23, 42, .24);
                height: 100vh;
                left: 0;
                max-width: calc(100vw - 48px);
                overflow-y: auto;
                padding-top: 64px;
                position: fixed;
                top: 0;
                transform: translateX(-105%);
                transition: transform .18s ease;
                width: 280px;
                z-index: 60;
            }
            body.agent-sidebar-open .sidebar {
                transform: translateX(0);
            }
            .sidebar .nav {
                grid-template-columns: 1fr;
            }
            .content {
                height: auto;
                min-height: 100vh;
                overflow: visible;
                padding: 64px 18px 22px;
            }
            .topbar.compact {
                margin-bottom: 14px;
                position: static;
            }
            .topbar,
            .topbar-actions {
                flex-wrap: wrap;
            }
            .agent-user-name,
            .agent-user-status {
                max-width: none;
            }
            .chat-layout {
                grid-template-columns: clamp(220px, 26vw, 260px) minmax(0, 1fr);
                height: auto;
                overflow: visible;
            }
            .closed-conversation-layout {
                grid-template-columns: 1fr;
                overflow: visible;
            }
            .closed-detail-panel {
                max-height: none;
            }
            .closed-conversation-layout .chat-conversation-header {
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .closed-download-actions {
                margin-left: 0;
            }
        }
        @media (max-width: 1000px) {
            .chat-layout {
                grid-template-columns: 1fr;
            }
            .chat-list-toggle {
                align-items: center;
                background: #fff;
                border: 1px solid var(--border);
                border-radius: 8px;
                color: #0f172a;
                display: inline-flex;
                font: inherit;
                font-weight: 600;
                gap: 8px;
                height: 42px;
                justify-self: start;
                padding: 0 13px;
            }
            .chat-list-toggle svg {
                color: #2563eb;
                height: 18px;
                width: 18px;
            }
            .chat-list-backdrop {
                background: rgba(15, 23, 42, .36);
                inset: 0;
                position: fixed;
                z-index: 48;
            }
            .chat-list-backdrop.open {
                display: block;
            }
            .conversation-list[data-chat-list-panel] {
                bottom: 0;
                box-shadow: 18px 0 42px rgba(15, 23, 42, .18);
                display: flex;
                height: 100vh;
                left: 0;
                max-height: none;
                max-width: calc(100vw - 42px);
                overflow: hidden;
                position: fixed;
                top: 0;
                transform: translateX(-105%);
                transition: transform .18s ease;
                width: min(320px, calc(100vw - 42px));
                z-index: 50;
            }
            body.chat-list-open .conversation-list[data-chat-list-panel] {
                transform: translateX(0);
            }
            .conversation-list[data-chat-list-panel] > [data-realtime-refresh] {
                overflow-y: auto;
            }
            .closed-chat-heading {
                align-items: flex-start;
                flex: 1 1 420px;
                flex-wrap: wrap;
            }
            .closed-download-actions {
                flex: 1 1 100%;
                justify-content: flex-start;
            }
            .closed-download-btn {
                width: auto;
            }
            .closed-summary-grid {
                grid-template-columns: repeat(auto-fit, minmax(min(210px, 100%), 1fr));
            }
        }
        @media (max-width: 767px) {
            .content {
                padding-left: 14px;
                padding-right: 14px;
            }
            .topbar {
                align-items: flex-start;
            }
            .agent-user-chip {
                min-width: 0;
            }
            .agent-user-name,
            .agent-user-status {
                max-width: min(180px, calc(100vw - 150px));
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .chat-panel {
                min-height: 0;
            }
            .closed-conversation-layout .chat-conversation-header {
                display: grid;
                grid-template-columns: 1fr;
            }
            .closed-chat-heading {
                display: grid;
                grid-template-columns: 1fr;
                width: 100%;
            }
            .closed-back-btn {
                justify-content: center;
                width: 100%;
            }
            .closed-title-line {
                gap: 8px;
            }
            .closed-title-line strong {
                font-size: 21px;
            }
            .closed-download-actions {
                display: grid;
                grid-template-columns: 1fr;
                width: 100%;
            }
            .closed-download-btn {
                justify-content: center;
                width: 100%;
            }
            .closed-summary-grid {
                grid-template-columns: repeat(auto-fit, minmax(min(180px, 100%), 1fr));
            }
        }
        @media (max-width: 425px) {
            .content {
                padding-left: 12px;
                padding-right: 12px;
            }
            .closed-summary-grid {
                grid-template-columns: 1fr;
            }
            .closed-summary-card {
                min-height: 72px;
            }
            .closed-detail-row {
                grid-template-columns: 36px minmax(0, 1fr);
            }
            .closed-detail-row > span:first-child {
                grid-column: 1 / -1;
            }
        }
    </style>
</head>
<body>
    <button class="agent-sidebar-toggle" type="button" data-agent-sidebar-toggle aria-label="Open navigation" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    </button>
    <button class="agent-sidebar-backdrop" type="button" data-agent-sidebar-backdrop aria-label="Close navigation" hidden></button>

    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <svg class="brand-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7.5 18.5 3 21v-4.5A8.5 8.5 0 0 1 4.5 5h12A4.5 4.5 0 0 1 21 9.5v3A4.5 4.5 0 0 1 16.5 17h-8a4.5 4.5 0 0 1-1-.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <div class="brand-title">Support Agent</div>
                    <div class="brand-subtitle">{{ auth()->user()->company?->name }}</div>
                </div>
            </div>
            <nav class="nav">
                <a href="{{ route('agent.dashboard') }}" @class(['active' => request()->routeIs('agent.dashboard')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m3 11 9-8 9 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5 10v10h14V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Dashboard</span>
                    </span>
                </a>
                <a href="{{ route('agent.waiting') }}" @class(['active' => request()->routeIs('agent.waiting')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>Waiting Chats</span>
                    </span>
                    <span class="nav-badge" data-realtime-refresh="nav-waiting-count">{{ $agentNavWaitingCount ?? 0 }}</span>
                </a>
                <a href="{{ route('agent.chats') }}" @class(['active' => request()->routeIs('agent.chats*')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 14a4 4 0 0 1-4 4H9l-6 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 9h8M8 13h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>My Chats</span>
                    </span>
                    <span class="nav-badge" data-realtime-refresh="nav-active-count">{{ $agentNavActiveCount ?? 0 }}</span>
                </a>
                <a href="{{ route('agent.closed') }}" @class(['active' => request()->routeIs('agent.closed')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 7h16v13H4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M8 7V4h8v3M8 12h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Closed Chats</span>
                    </span>
                </a>
                <a href="{{ route('agent.missed-chats') }}" @class(['active' => request()->routeIs('agent.missed-chats*')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 8v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Missed Chats</span>
                    </span>
                    <span class="nav-badge">{{ $agentNavMissedCount ?? 0 }}</span>
                </a>
                <a href="{{ route('agent.notifications') }}" @class(['active' => request()->routeIs('agent.notifications*')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>Notifications</span>
                    </span>
                    <span class="nav-badge" data-agent-notification-nav-count @if(($agentNavNotificationCount ?? 0) < 1) hidden @endif>{{ $agentNavNotificationCount ?? 0 }}</span>
                </a>
                <a href="{{ route('agent.canned-replies') }}" @class(['active' => request()->routeIs('agent.canned-replies*')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 14a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 9h7M8 13h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>Canned Replies</span>
                    </span>
                </a>
                <a href="{{ route('agent.profile') }}" @class(['active' => request()->routeIs('agent.profile*')])>
                    <span class="nav-label">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>Profile</span>
                    </span>
                </a>
                <form class="availability-box" method="POST" action="{{ route('agent.availability.update') }}">
                    @csrf
                    <label for="agent-availability">Availability</label>
                    <select
                        id="agent-availability"
                        name="availability_status"
                        onchange="this.form.submit()"
                    >
                        @foreach (['online' => 'Online', 'away' => 'On Break', 'offline' => 'Offline'] as $value => $label)
                            <option value="{{ $value }}" @selected((auth()->user()->availability_status ?? 'offline') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <form id="agent-logout-form" method="POST" action="{{ route('agent.logout') }}">
                    @csrf
                    <button type="button" data-open-logout-modal>
                        <span class="nav-label">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M14 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 12h12M17 8l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Logout</span>
                        </span>
                    </button>
                </form>
            </nav>
        </aside>

        <main class="content">
            @php($availability = auth()->user()->availability_status ?? 'offline')
            @php($agentInitials = collect(explode(' ', trim(auth()->user()->name ?? 'Agent')))->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode(''))
            @php($hideTopbarTitle = request()->routeIs('agent.dashboard') || request()->routeIs('agent.chats') || request()->routeIs('agent.closed*') || request()->routeIs('agent.missed-chats*') || request()->routeIs('agent.notifications*') || request()->routeIs('agent.canned-replies*'))
            <div @class(['topbar', 'compact' => $hideTopbarTitle])>
                @if ($hideTopbarTitle)
                    <div class="topbar-spacer" hidden></div>
                @else
                    <div>
                        <h1>{{ $title ?? 'Agent Dashboard' }}</h1>
                        @if (request()->routeIs('agent.profile*'))
                            <div class="muted">Support Agent <span aria-hidden="true">&rsaquo;</span> Profile</div>
                        @elseif (request()->routeIs('agent.waiting'))
                            <div class="muted">Review visitors waiting for support.</div>
                        @else
                            <div class="muted">
                                {{ auth()->user()->company?->name }}
                                <span class="availability-dot {{ $availability }}"></span>{{ $availability === 'away' ? 'On Break' : ucfirst($availability) }}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="topbar-actions">
                    <div class="agent-notification-wrap">
                        <button class="agent-notification-button" type="button" data-agent-notification-bell title="Notifications" aria-haspopup="true" aria-expanded="false">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <span class="agent-notification-badge" data-agent-notification-count @if(($agentNavNotificationCount ?? 0) < 1) hidden @endif>{{ ($agentNavNotificationCount ?? 0) > 9 ? '9+' : ($agentNavNotificationCount ?? 0) }}</span>
                        </button>
                        <div class="agent-notification-dropdown" data-agent-notification-dropdown hidden>
                            <div class="agent-notification-dropdown-header">
                                <strong>Notifications</strong>
                                <button type="button" data-agent-notification-mark-all>Mark all as read</button>
                            </div>
                            <div class="agent-notification-dropdown-list" data-agent-notification-latest>
                                @forelse(($agentLatestNotifications ?? collect()) as $notification)
                                    <a class="agent-notification-mini {{ $notification->is_read ? '' : 'unread' }}" href="{{ $notification->data['action_url'] ?? route('agent.notifications') }}">
                                        <strong>{{ $notification->title }}</strong>
                                        <span>{{ \Illuminate\Support\Str::limit($notification->message, 82) }}</span>
                                    </a>
                                @empty
                                    <div class="agent-notification-mini"><span>No notifications yet.</span></div>
                                @endforelse
                            </div>
                            <a class="agent-notification-dropdown-footer" href="{{ route('agent.notifications') }}">View All</a>
                        </div>
                    </div>

                    <div class="agent-user-chip">
                        <span class="agent-avatar">{{ \Illuminate\Support\Str::upper($agentInitials ?: 'A') }}</span>
                        <span>
                            <span class="agent-user-name">{{ auth()->user()->name }}</span>
                            <span class="agent-user-status {{ $availability }}">{{ $availability === 'away' ? 'On Break' : ucfirst($availability) }}</span>
                        </span>
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

    <div class="logout-modal-backdrop" data-logout-modal hidden>
        <div
            class="logout-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="logout-modal-title"
        >
            <h2 id="logout-modal-title">Are you sure?</h2>
            <p>You will be marked offline and returned to the login screen.</p>
            <div class="logout-modal-actions">
                <button class="btn gray" type="button" data-close-logout-modal>Cancel</button>
                <button class="btn danger" type="button" data-confirm-logout>Logout</button>
            </div>
        </div>
    </div>

    <button class="agent-live-toast" type="button" data-agent-live-toast hidden>
        <span data-agent-live-toast-count>1</span>
        <strong data-agent-live-toast-text>New visitor message</strong>
    </button>

    <script>
        window.AgentRealtime = {
            enabled: @json(config('broadcasting.default') === 'reverb' && filled(config('broadcasting.connections.reverb.key'))),
            key: @json(config('broadcasting.connections.reverb.key')),
            host: @json(config('broadcasting.connections.reverb.options.host')),
            port: @json((int) config('broadcasting.connections.reverb.options.port')),
            scheme: @json(config('broadcasting.connections.reverb.options.scheme')),
            agentId: @json(auth()->id()),
            companyId: @json(auth()->user()?->company_id),
            authEndpoint: @json(url('/broadcasting/auth')),
            csrfToken: @json(csrf_token()),
            notificationUnreadCount: @json($agentNavNotificationCount ?? 0),
            notificationLatestUrl: @json(route('agent.notifications.latest')),
            notificationMarkAllUrl: @json(route('agent.notifications.mark-all-read')),
            activeConversations: @json($agentLiveNotificationConversations ?? []),
            chatShowUrlTemplate: @json(url('/agent/chats/__CONVERSATION_ID__')),
            notificationIcon: @json(asset('favicon.ico')),
        };
    </script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.2.4/dist/echo.iife.js"></script>
    <script>
        (() => {
            const config = window.AgentRealtime || {};
            const logoutForm = document.querySelector('#agent-logout-form');
            const logoutModal = document.querySelector('[data-logout-modal]');
            const openLogoutModal = document.querySelector('[data-open-logout-modal]');
            const closeLogoutModal = document.querySelector('[data-close-logout-modal]');
            const confirmLogout = document.querySelector('[data-confirm-logout]');
            const liveToast = document.querySelector('[data-agent-live-toast]');
            const liveToastText = document.querySelector('[data-agent-live-toast-text]');
            const liveToastCount = document.querySelector('[data-agent-live-toast-count]');
            const notificationBell = document.querySelector('[data-agent-notification-bell]');
            const notificationCount = document.querySelector('[data-agent-notification-count]');
            const notificationNavCount = document.querySelector('[data-agent-notification-nav-count]');
            const notificationDropdown = document.querySelector('[data-agent-notification-dropdown]');
            const notificationLatest = document.querySelector('[data-agent-notification-latest]');
            const notificationMarkAll = document.querySelector('[data-agent-notification-mark-all]');
            const sidebarToggle = document.querySelector('[data-agent-sidebar-toggle]');
            const sidebarBackdrop = document.querySelector('[data-agent-sidebar-backdrop]');
            const chatListToggle = document.querySelector('[data-chat-list-toggle]');
            const chatListBackdrop = document.querySelector('[data-chat-list-backdrop]');
            const chatListPanel = document.querySelector('[data-chat-list-panel]');
            const originalTitle = document.title;
            const unreadStorageKey = config.agentId
                ? `agent_live_unread_${config.agentId}`
                : null;
            let liveToastUrl = null;
            let liveToastTimeout = null;
            let unreadVisitorMessages = 0;
            let unreadByConversation = {};
            let persistentUnreadNotifications = Number(config.notificationUnreadCount || 0);
            let notificationAudioContext = null;
            const desktopNotificationIds = new Set();
            const desktopPermissionKey = config.agentId
                ? `agent_desktop_notifications_requested_${config.agentId}`
                : null;
            let subscribeVisibleChatNotifications = () => {};

            const setSidebarOpen = open => {
                document.body.classList.toggle('agent-sidebar-open', open);
                sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');

                if (sidebarBackdrop) {
                    sidebarBackdrop.hidden = !open;
                    sidebarBackdrop.classList.toggle('open', open);
                }
            };

            const setChatListOpen = open => {
                document.body.classList.toggle('chat-list-open', open);
                chatListToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');

                if (chatListBackdrop) {
                    chatListBackdrop.hidden = !open;
                    chatListBackdrop.classList.toggle('open', open);
                }
            };

            sidebarToggle?.addEventListener('click', () => {
                setSidebarOpen(!document.body.classList.contains('agent-sidebar-open'));
            });

            sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));

            document.querySelectorAll('.sidebar a, .sidebar button').forEach(element => {
                element.addEventListener('click', () => setSidebarOpen(false));
            });

            chatListToggle?.addEventListener('click', () => {
                setChatListOpen(!document.body.classList.contains('chat-list-open'));
            });

            chatListBackdrop?.addEventListener('click', () => setChatListOpen(false));

            chatListPanel?.querySelectorAll('a, button').forEach(element => {
                element.addEventListener('click', () => setChatListOpen(false));
            });

            document.addEventListener('keydown', event => {
                if (event.key !== 'Escape') {
                    return;
                }

                setSidebarOpen(false);
                setChatListOpen(false);
            });

            const showLogoutModal = () => {
                if (!logoutModal) {
                    return;
                }

                logoutModal.hidden = false;
                logoutModal.classList.add('open');
                closeLogoutModal?.focus();
            };

            const hideLogoutModal = () => {
                if (!logoutModal) {
                    return;
                }

                logoutModal.classList.remove('open');
                logoutModal.hidden = true;
                openLogoutModal?.focus();
            };

            openLogoutModal?.addEventListener('click', showLogoutModal);
            closeLogoutModal?.addEventListener('click', hideLogoutModal);
            confirmLogout?.addEventListener('click', () => {
                logoutForm?.submit();
            });
            logoutModal?.addEventListener('click', event => {
                if (event.target === logoutModal) {
                    hideLogoutModal();
                }
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && logoutModal?.classList.contains('open')) {
                    hideLogoutModal();
                }
            });

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

            const currentOpenConversationId = () =>
                document.querySelector('.chat-panel[data-conversation-id]')?.dataset.conversationId || null;

            const openConversationStorageKey = config.agentId
                ? `agent_open_conversation_${config.agentId}`
                : null;

            const currentTabId = `${Date.now()}-${Math.random().toString(36).slice(2)}`;
            const openConversationHeartbeatMs = 5000;
            const openConversationExpiryMs = 15000;

            const storeOpenConversation = () => {
                const conversationId = currentOpenConversationId();

                if (!openConversationStorageKey || !conversationId) {
                    return;
                }

                localStorage.setItem(openConversationStorageKey, JSON.stringify({
                    conversationId: String(conversationId),
                    tabId: currentTabId,
                    updatedAt: Date.now(),
                }));
            };

            const storedOpenConversationId = () => {
                if (!openConversationStorageKey) {
                    return null;
                }

                try {
                    const stored = JSON.parse(localStorage.getItem(openConversationStorageKey) || '{}');

                    if (
                        !stored?.conversationId
                        || !stored?.updatedAt
                        || Date.now() - Number(stored.updatedAt) > openConversationExpiryMs
                    ) {
                        return null;
                    }

                    return String(stored.conversationId);
                } catch (error) {
                    return null;
                }
            };

            const isConversationOpenForAgent = conversationId => {
                const targetConversationId = String(conversationId || '');

                if (!targetConversationId) {
                    return false;
                }

                return String(currentOpenConversationId() || '') === targetConversationId
                    || String(storedOpenConversationId() || '') === targetConversationId;
            };

            storeOpenConversation();

            if (currentOpenConversationId()) {
                setInterval(storeOpenConversation, openConversationHeartbeatMs);

                window.addEventListener('beforeunload', () => {
                    if (!openConversationStorageKey) {
                        return;
                    }

                    try {
                        const stored = JSON.parse(localStorage.getItem(openConversationStorageKey) || '{}');

                        if (stored?.tabId === currentTabId) {
                            localStorage.removeItem(openConversationStorageKey);
                        }
                    } catch (error) {
                        localStorage.removeItem(openConversationStorageKey);
                    }
                });
            }

            const truncatePreview = (value, length = 70) => {
                const text = String(value || '').trim();

                if (text.length <= length) {
                    return text;
                }

                return `${text.slice(0, length - 1).trimEnd()}...`;
            };

            const formatBrowserTime = value => {
                if (!value) {
                    return '';
                }

                const date = new Date(value);

                if (Number.isNaN(date.getTime())) {
                    return '';
                }

                return date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                });
            };

            const updateConversationUnreadBadge = (conversationId, count) => {
                const selectorId = String(conversationId || '').replace(/"/g, '\\"');

                if (!selectorId) {
                    return;
                }

                document
                    .querySelectorAll(`[data-conversation-id="${selectorId}"]`)
                    .forEach(item => {
                        let badge = item.querySelector('[data-conversation-unread-count]');

                        if (count < 1) {
                            badge?.remove();
                            return;
                        }

                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'conversation-unread-count';
                            badge.dataset.conversationUnreadCount = 'true';

                            const target = item.querySelector('.conversation-link-meta, .item-actions, .dashboard-chat-meta, .my-chat-side')
                                || item;

                            target.prepend(badge);
                        }

                        badge.textContent = count > 9 ? '9+' : String(count);
                    });
            };

            const updateConversationPreview = event => {
                if (!event?.conversation_id) {
                    return;
                }

                const conversationId = String(event.conversation_id);
                const selectorId = conversationId.replace(/"/g, '\\"');
                const message = String(event.message || '').trim()
                    || (event.attachment?.name ? `Attachment: ${event.attachment.name}` : 'Attachment');
                const time = formatBrowserTime(event.created_at);
                const createdDate = event.created_at ? new Date(event.created_at) : null;
                const dateTime = createdDate && !Number.isNaN(createdDate.getTime())
                    ? createdDate.toISOString()
                    : '';

                document
                    .querySelectorAll(`[data-conversation-id="${selectorId}"]`)
                    .forEach(item => {
                        item.dataset.updatedAt = event.created_at && dateTime
                            ? String(Math.floor(createdDate.getTime() / 1000))
                            : item.dataset.updatedAt || '0';

                        let previewTargets = item.querySelectorAll([
                            '.conversation-last-message',
                            '.item-last-message span',
                            '.my-chat-preview',
                            '.dashboard-chat-message',
                        ].join(','));

                        if (!previewTargets.length) {
                            const sidebarMain = item.querySelector('.conversation-link-main');

                            if (sidebarMain) {
                                const preview = document.createElement('div');
                                preview.className = 'conversation-last-message';
                                sidebarMain.appendChild(preview);
                                previewTargets = [preview];
                            }
                        }

                        previewTargets.forEach(target => {
                            target.textContent = truncatePreview(
                                message,
                                target.classList.contains('my-chat-preview') ? 100 : 70
                            );
                        });

                        item.querySelectorAll([
                            '.conversation-link-meta time',
                            '.item-last-message time',
                            '.dashboard-chat-time',
                            '.my-chat-date',
                        ].join(',')).forEach(target => {
                            if (dateTime && target.tagName === 'TIME') {
                                target.setAttribute('datetime', dateTime);
                            }

                            if (target.classList.contains('my-chat-date')) {
                                target.textContent = formatBrowserDateTime(event.created_at) || target.textContent;
                                return;
                            }

                            target.textContent = time || target.textContent;
                        });
                    });
            };

            const storedUnreadNotification = () => {
                if (!unreadStorageKey) {
                    return { conversations: {} };
                }

                try {
                    const stored = JSON.parse(localStorage.getItem(unreadStorageKey) || '{}') || {};

                    if (stored.conversations && typeof stored.conversations === 'object') {
                        return stored;
                    }

                    return {
                        conversations: stored.count && stored.url
                            ? {
                                global: {
                                    count: Number(stored.count || 0),
                                    label: stored.label || 'visitor',
                                    url: stored.url,
                                },
                            }
                            : {},
                    };
                } catch (error) {
                    return { conversations: {} };
                }
            };

            const totalUnreadVisitorMessages = () =>
                Object.values(unreadByConversation).reduce(
                    (total, item) => total + Number(item?.count || 0),
                    0
                );

            const persistUnreadNotification = () => {
                if (!unreadStorageKey) {
                    return;
                }

                if (totalUnreadVisitorMessages() < 1) {
                    localStorage.removeItem(unreadStorageKey);
                    return;
                }

                localStorage.setItem(
                    unreadStorageKey,
                    JSON.stringify({
                        conversations: unreadByConversation,
                    })
                );
            };

            const updateTitleBadge = () => {
                const totalUnread = Math.max(totalUnreadVisitorMessages(), persistentUnreadNotifications);
                const notificationUnread = persistentUnreadNotifications;

                document.title = totalUnread > 0
                    ? `(${totalUnread > 9 ? '9+' : totalUnread}) ${originalTitle}`
                    : originalTitle;

                if (notificationCount) {
                    notificationCount.hidden = notificationUnread < 1;
                    notificationCount.textContent = notificationUnread > 9
                        ? '9+'
                        : String(notificationUnread);
                }
                if (notificationNavCount) {
                    notificationNavCount.hidden = notificationUnread < 1;
                    notificationNavCount.textContent = notificationUnread > 9
                        ? '9+'
                        : String(notificationUnread);
                }
            };

            const renderNotificationDropdown = notifications => {
                if (!notificationLatest) {
                    return;
                }

                if (!notifications?.length) {
                    notificationLatest.innerHTML = '<div class="agent-notification-mini"><span>No notifications yet.</span></div>';
                    return;
                }

                notificationLatest.innerHTML = notifications.map(item => `
                    <a class="agent-notification-mini ${item.is_read ? '' : 'unread'}" href="${item.action_url || '#'}">
                        <strong>${item.title || 'Notification'}</strong>
                        <span>${item.message || ''}</span>
                    </a>
                `).join('');
            };

            const loadLatestNotifications = () => {
                if (!config.notificationLatestUrl) {
                    return;
                }

                fetch(config.notificationLatestUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        persistentUnreadNotifications = Number(data.unread_count || 0);
                        renderNotificationDropdown(data.notifications || []);
                        updateTitleBadge();
                    })
                    .catch(() => {});
            };

            const toggleNotificationDropdown = event => {
                event?.stopPropagation();

                if (!notificationDropdown) {
                    if (config.notificationLatestUrl) {
                        window.location.href = @json(route('agent.notifications'));
                    }
                    return;
                }

                const nextHidden = !notificationDropdown.hidden ? true : false;
                notificationDropdown.hidden = nextHidden;
                notificationBell?.setAttribute('aria-expanded', nextHidden ? 'false' : 'true');

                if (!nextHidden) {
                    loadLatestNotifications();
                }
            };

            const hideAgentLiveToast = () => {
                if (!liveToast) {
                    return;
                }

                liveToast.hidden = true;
                clearTimeout(liveToastTimeout);
                liveToastTimeout = null;
            };

            const clearAgentLiveNotification = (conversationId = null) => {
                if (conversationId) {
                    delete unreadByConversation[String(conversationId)];
                    updateConversationUnreadBadge(conversationId, 0);
                } else {
                    Object.keys(unreadByConversation).forEach(id => {
                        updateConversationUnreadBadge(id, 0);
                    });
                    unreadByConversation = {};
                }

                unreadVisitorMessages = totalUnreadVisitorMessages();
                persistUnreadNotification();
                updateTitleBadge();
                hideAgentLiveToast();
            };

            const ensureNotificationAudio = () => {
                const AudioContextCtor = window.AudioContext || window.webkitAudioContext;

                if (!AudioContextCtor) {
                    return null;
                }

                notificationAudioContext = notificationAudioContext || new AudioContextCtor();

                if (notificationAudioContext.state === 'suspended') {
                    notificationAudioContext.resume().catch(() => {});
                }

                return notificationAudioContext;
            };

            const playAgentNotificationSound = () => {
                try {
                    const audioContext = ensureNotificationAudio();

                    if (!audioContext) {
                        return;
                    }

                    const playTone = (frequency, startOffset, duration) => {
                        const oscillator = audioContext.createOscillator();
                        const gain = audioContext.createGain();
                        const startAt = audioContext.currentTime + startOffset;

                        oscillator.type = 'sine';
                        oscillator.frequency.setValueAtTime(frequency, startAt);
                        gain.gain.setValueAtTime(0.0001, startAt);
                        gain.gain.exponentialRampToValueAtTime(0.16, startAt + 0.015);
                        gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);

                        oscillator.connect(gain);
                        gain.connect(audioContext.destination);
                        oscillator.start(startAt);
                        oscillator.stop(startAt + duration + 0.02);
                    };

                    playTone(880, 0, 0.22);
                    playTone(1175, 0.16, 0.28);
                } catch (error) {
                    // Browser may block audio until the agent interacts with the page.
                }
            };

            const requestDesktopNotificationPermission = () => {
                if (!('Notification' in window) || Notification.permission !== 'default') {
                    return;
                }

                if (desktopPermissionKey && localStorage.getItem(desktopPermissionKey) === 'true') {
                    return;
                }

                if (desktopPermissionKey) {
                    localStorage.setItem(desktopPermissionKey, 'true');
                }

                try {
                    Notification.requestPermission().then(permission => {
                        if (permission === 'default' && desktopPermissionKey) {
                            localStorage.removeItem(desktopPermissionKey);
                        }
                    }).catch(() => {
                        if (desktopPermissionKey) {
                            localStorage.removeItem(desktopPermissionKey);
                        }
                    });
                } catch (error) {
                    // Desktop notifications are optional; sound and badges continue to work.
                    if (desktopPermissionKey) {
                        localStorage.removeItem(desktopPermissionKey);
                    }
                }
            };

            const shouldShowDesktopNotification = conversationId => {
                if (isConversationOpenForAgent(conversationId)) {
                    return false;
                }

                return true;
            };

            const showDesktopVisitorNotification = ({ messageId, conversationId, visitorName, url }) => {
                if (
                    !('Notification' in window)
                    || Notification.permission !== 'granted'
                    || !messageId
                    || desktopNotificationIds.has(String(messageId))
                    || !shouldShowDesktopNotification(conversationId)
                ) {
                    return;
                }

                desktopNotificationIds.add(String(messageId));

                if (desktopNotificationIds.size > 100) {
                    desktopNotificationIds.delete(desktopNotificationIds.values().next().value);
                }

                try {
                    const desktopNotification = new Notification('New Visitor Message', {
                        body: `${visitorName || 'Visitor'} sent you a new message.`,
                        icon: config.notificationIcon || '/favicon.ico',
                        badge: config.notificationIcon || '/favicon.ico',
                        tag: `visitor-message-${messageId}`,
                        renotify: false,
                    });

                    desktopNotification.addEventListener('click', () => {
                        window.focus();

                        if (url) {
                            window.location.href = url;
                        }

                        desktopNotification.close();
                    });
                } catch (error) {
                    // Native notification may be blocked by the browser or OS settings.
                }
            };

            const showAgentLiveNotification = notification => {
                const conversationId = String(notification.id || '');

                if (!liveToast || !conversationId || isConversationOpenForAgent(conversationId)) {
                    return;
                }

                const unreadForConversation =
                    Number(unreadByConversation[conversationId]?.count || 0) + 1;

                unreadByConversation[conversationId] = {
                    count: unreadForConversation,
                    label: notification.label || 'visitor',
                    url: notification.url,
                };

                unreadVisitorMessages = totalUnreadVisitorMessages();
                liveToastUrl = notification.url;
                persistUnreadNotification();
                updateConversationUnreadBadge(conversationId, unreadForConversation);

                if (liveToastCount) {
                    liveToastCount.textContent = unreadForConversation > 9
                        ? '9+'
                        : String(unreadForConversation);
                }

                if (liveToastText) {
                    liveToastText.textContent = `New message from ${notification.label || 'visitor'}`;
                }

                liveToast.hidden = false;
                updateTitleBadge();
                playAgentNotificationSound();
                showDesktopVisitorNotification({
                    messageId: notification.messageId,
                    conversationId,
                    visitorName: notification.label,
                    url: notification.url,
                });

                clearTimeout(liveToastTimeout);
                liveToastTimeout = setTimeout(hideAgentLiveToast, 4000);
            };

            const restoreAgentLiveNotification = () => {
                const stored = storedUnreadNotification();
                unreadByConversation = stored.conversations || {};
                unreadVisitorMessages = totalUnreadVisitorMessages();
                Object.entries(unreadByConversation).forEach(([id, item]) => {
                    updateConversationUnreadBadge(id, Number(item?.count || 0));
                });

                if (unreadVisitorMessages < 1) {
                    unreadVisitorMessages = 0;
                    persistUnreadNotification();
                    updateTitleBadge();
                    return;
                }

                const latest = Object.values(unreadByConversation)
                    .filter(item => Number(item?.count || 0) > 0)
                    .at(-1);

                liveToastUrl = latest?.url || null;

                if (liveToastCount) {
                    const latestCount = Number(latest?.count || unreadVisitorMessages);

                    liveToastCount.textContent = latestCount > 9
                        ? '9+'
                        : String(latestCount);
                }

                if (liveToastText) {
                    liveToastText.textContent = `New message from ${latest?.label || 'visitor'}`;
                }

                updateTitleBadge();
            };

            restoreAgentLiveNotification();

            if (currentOpenConversationId()) {
                clearAgentLiveNotification(currentOpenConversationId());
            }

            ['pointerdown', 'keydown'].forEach(eventName => {
                document.addEventListener(eventName, ensureNotificationAudio, { once: true });
                document.addEventListener(eventName, requestDesktopNotificationPermission, { once: true });
            });
            requestDesktopNotificationPermission();

            liveToast?.addEventListener('click', () => {
                const url = liveToastUrl;
                const conversationId = Object.entries(unreadByConversation)
                    .find(([, item]) => item?.url === url)?.[0] || null;

                clearAgentLiveNotification(conversationId);

                if (url) {
                    window.location.href = url;
                }
            });

            notificationBell?.addEventListener('click', toggleNotificationDropdown);
            notificationDropdown?.addEventListener('click', event => event.stopPropagation());
            document.addEventListener('click', () => {
                if (notificationDropdown) {
                    notificationDropdown.hidden = true;
                    notificationBell?.setAttribute('aria-expanded', 'false');
                }
            });
            notificationMarkAll?.addEventListener('click', () => {
                if (!config.notificationMarkAllUrl) {
                    return;
                }

                notificationMarkAll.disabled = true;

                fetch(config.notificationMarkAllUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        persistentUnreadNotifications = Number(data.unread_count || 0);
                        notificationLatest?.querySelectorAll('.agent-notification-mini.unread').forEach(item => {
                            item.classList.remove('unread');
                        });
                        document.querySelectorAll('.notification-card.unread').forEach(card => {
                            card.classList.remove('unread');
                            card.classList.add('read');
                            card.querySelector('.notification-dot')?.remove();
                        });
                        updateTitleBadge();
                        loadLatestNotifications();
                    })
                    .catch(() => {})
                    .finally(() => {
                        notificationMarkAll.disabled = false;
                    });
            });

            const visitorModal = document.querySelector('[data-visitor-modal]');
            const visitorModalName = visitorModal?.querySelector('input[name="name"]');
            const openVisitorModal = () => {
                if (!visitorModal) {
                    return;
                }

                visitorModal.hidden = false;
                window.setTimeout(() => visitorModalName?.focus(), 20);
            };
            const closeVisitorModal = () => {
                if (visitorModal) {
                    visitorModal.hidden = true;
                }
            };

            document.querySelectorAll('[data-visitor-modal-open]').forEach(button => {
                button.addEventListener('click', openVisitorModal);
            });
            document.querySelectorAll('[data-visitor-modal-close]').forEach(button => {
                button.addEventListener('click', closeVisitorModal);
            });
            visitorModal?.addEventListener('click', event => {
                if (event.target === visitorModal) {
                    closeVisitorModal();
                }
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && visitorModal && !visitorModal.hidden) {
                    closeVisitorModal();
                }
            });

            document.querySelectorAll('[data-agent-chat-status-form]').forEach(form => {
                const input = form.querySelector('[data-agent-chat-status-input]');
                const trigger = form.querySelector('[data-agent-chat-status-trigger]');
                const current = form.querySelector('[data-agent-chat-status-current]');
                const menu = form.querySelector('[data-agent-chat-status-menu]');
                const options = form.querySelectorAll('[data-agent-chat-status-option]');
                const error = form.querySelector('[data-agent-chat-status-error]');

                if (!input || !trigger || !menu) {
                    return;
                }

                let lastValue = input.value;

                const closeMenu = () => {
                    menu.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                };

                const openMenu = () => {
                    menu.hidden = false;
                    trigger.setAttribute('aria-expanded', 'true');
                };

                trigger.addEventListener('click', event => {
                    event.stopPropagation();
                    menu.hidden ? openMenu() : closeMenu();
                });

                menu.addEventListener('click', event => {
                    event.stopPropagation();
                });

                options.forEach(option => {
                    option.addEventListener('click', async () => {
                        const selectedValue = option.dataset.agentChatStatusOption;
                        const selectedLabel = option.dataset.agentChatStatusLabel || option.textContent.trim();

                        if (!selectedValue || selectedValue === input.value) {
                            closeMenu();
                            return;
                        }

                        input.value = selectedValue;
                        closeMenu();

                    if (error) {
                        error.hidden = true;
                        error.textContent = '';
                    }

                    trigger.disabled = true;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(data.message || 'Chat status could not be updated.');
                        }

                        lastValue = data.agent_chat_status || input.value;
                        input.value = lastValue;
                        form.classList.remove('active', 'on_hold', 'awaiting_visitor');
                        form.classList.add(lastValue);
                        if (current) {
                            current.textContent = data.label || selectedLabel;
                        }
                        options.forEach(item => {
                            item.setAttribute(
                                'aria-selected',
                                item.dataset.agentChatStatusOption === lastValue ? 'true' : 'false'
                            );
                        });
                        document
                            .querySelectorAll(`[data-agent-chat-status-badge="${input.dataset.conversationId}"]`)
                            .forEach(badge => {
                                badge.textContent = data.label || selectedLabel;
                                badge.className = `agent-chat-status-badge ${lastValue}`;
                            });
                    } catch (updateError) {
                        input.value = lastValue;

                        if (error) {
                            error.textContent = updateError.message;
                            error.hidden = false;
                        }
                    } finally {
                        trigger.disabled = false;
                    }
                    });
                });
            });

            document.addEventListener('click', () => {
                document.querySelectorAll('[data-agent-chat-status-menu]').forEach(menu => {
                    menu.hidden = true;
                });
                document.querySelectorAll('[data-agent-chat-status-trigger]').forEach(trigger => {
                    trigger.setAttribute('aria-expanded', 'false');
                });
            });

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
                            target.dispatchEvent(new CustomEvent('agent:realtime-refreshed', { bubbles: true }));
                        }
                    });

                    subscribeVisibleChatNotifications();
                })
                    .catch(() => {});
            };

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
                    enabledTransports: ['ws', 'wss'],
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
                const subscribedNotificationChannels = new Set();

                const markNotificationRead = notificationId => {
                    if (!notificationId || !config.csrfToken) {
                        return;
                    }

                    const body = new FormData();
                    body.append('action', 'mark_read');
                    body.append('notifications[]', notificationId);

                    fetch(@json(route('agent.notifications.bulk')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body,
                    }).catch(() => {});
                };

                const showPersistentNotification = event => {
                    if (
                        event?.conversation_id
                        && isConversationOpenForAgent(event.conversation_id)
                    ) {
                        markNotificationRead(event.id);
                        loadLatestNotifications();
                        return;
                    }

                    persistentUnreadNotifications = Number(event.unread_count || persistentUnreadNotifications + 1);
                    updateTitleBadge();
                    loadLatestNotifications();

                    liveToastUrl = event.action_url || @json(route('agent.notifications'));

                    if (liveToastCount) {
                        liveToastCount.textContent = persistentUnreadNotifications > 9 ? '9+' : String(persistentUnreadNotifications);
                    }

                    if (liveToastText) {
                        liveToastText.textContent = event.title || 'New notification';
                    }

                    if (liveToast) {
                        liveToast.hidden = false;
                        clearTimeout(liveToastTimeout);
                        liveToastTimeout = setTimeout(hideAgentLiveToast, 4000);
                    }

                    playAgentNotificationSound();
                };

                if (config.agentId) {
                    echo.private(`agent-notifications.${config.agentId}`)
                        .listen('.AgentNotificationCreated', showPersistentNotification);
                }

                const subscribeToLiveChatNotification = notification => {
                    const conversationId = notification.id;

                    if (
                        !conversationId
                        || subscribedNotificationChannels.has(String(conversationId))
                        || String(conversationId) === String(currentOpenConversationId())
                    ) {
                        return;
                    }

                    subscribedNotificationChannels.add(String(conversationId));

                    echo.private(`live-chat.${conversationId}`)
                        .listen('.LiveChatMessageSent', event => {
                            updateConversationPreview(event);

                            if (
                                String(event.conversation_id) !== String(conversationId)
                                || !['visitor', 'user'].includes(event.sender_type)
                                || String(conversationId) === String(currentOpenConversationId())
                            ) {
                                return;
                            }

                            refreshTargets();
                            showAgentLiveNotification({
                                ...notification,
                                messageId: event.message_id,
                            });
                        })
                        .listen('.LiveChatClosed', refreshTargets);
                };

                if (config.companyId) {
                    echo.private(`company-live-chat.${config.companyId}`)
                        .listen('.LiveChatRequested', refreshTargets)
                        .listen('.AgentJoinedConversation', event => {
                            refreshTargets();

                            if (String(event.agent_id) !== String(config.agentId)) {
                                return;
                            }

                            subscribeToLiveChatNotification({
                                id: event.conversation_id,
                                label: 'Visitor',
                                url: String(config.chatShowUrlTemplate || '').replace(
                                    '__CONVERSATION_ID__',
                                    event.conversation_id
                                ),
                            });
                        })
                        .listen('.LiveChatClosed', refreshTargets);
                }

                subscribeVisibleChatNotifications = () => {
                    (config.activeConversations || []).forEach(conversation => {
                        subscribeToLiveChatNotification({
                            id: conversation.id,
                            label: conversation.label,
                            url: conversation.url,
                        });
                    });

                    document
                        .querySelectorAll('[data-agent-live-chat-link][data-conversation-id]')
                        .forEach(link => {
                            subscribeToLiveChatNotification({
                                id: link.dataset.conversationId,
                                label: link.dataset.visitorLabel,
                                url: link.href,
                            });
                        });
                };

                subscribeVisibleChatNotifications();

                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', () => {
                        form.querySelectorAll('button[type="submit"]').forEach(button => {
                            button.disabled = true;
                        });
                    });
                });

                const chatPanel = document.querySelector('.chat-panel[data-conversation-id]');

                if (!chatPanel) {
                    return;
                }

                const conversationId = chatPanel.dataset.conversationId;
                const messages = document.querySelector('[data-message-list]');
                const composer = document.querySelector('[data-message-composer]');
                const visitorTyping = document.querySelector('[data-visitor-typing]');
                const newMessagesButton = document.querySelector('[data-new-messages-button]');
                const newMessagesCount = document.querySelector('[data-new-messages-count]');
                let visitorTypingTimeout = null;
                let agentTypingThrottle = null;
                let pendingOpenConversationMessages = 0;
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

                const setOpenConversationUnread = count => {
                    pendingOpenConversationMessages = Math.max(0, Number(count || 0));
                    updateConversationUnreadBadge(conversationId, pendingOpenConversationMessages);

                    if (!newMessagesButton) {
                        return;
                    }

                    newMessagesButton.hidden = pendingOpenConversationMessages < 1;

                    if (newMessagesCount) {
                        newMessagesCount.textContent = pendingOpenConversationMessages > 9
                            ? '9+'
                            : String(pendingOpenConversationMessages);
                    }
                };

                newMessagesButton?.addEventListener('click', () => {
                    if (!messages) {
                        return;
                    }

                    messages.scrollTop = messages.scrollHeight;
                    setOpenConversationUnread(0);
                });

                messages?.addEventListener('scroll', () => {
                    if (nearBottom()) {
                        setOpenConversationUnread(0);
                    }
                }, { passive: true });

                const initializeVoicePlayer = player => {
                    if (!player || player.dataset.voiceReady === 'true') {
                        return;
                    }

                    const audio = player.querySelector('.voice-audio');
                    const button = player.querySelector('.voice-play');
                    const track = player.querySelector('.voice-track');
                    const fill = player.querySelector('.voice-progress');
                    const time = player.querySelector('.voice-time');
                    const duration = Number(player.dataset.duration || 0);

                    if (!audio || !button || !track || !fill || !time) {
                        return;
                    }

                    const formatTime = seconds => {
                        const value = Number.isFinite(seconds) && seconds > 0 ? seconds : 0;
                        return `${Math.floor(value / 60)}:${String(Math.floor(value % 60)).padStart(2, '0')}`;
                    };

                    const updateTime = () => {
                        const total = Number.isFinite(audio.duration) && audio.duration > 0
                            ? audio.duration
                            : duration;
                        const current = audio.currentTime || 0;
                        const progress = total > 0 ? Math.min((current / total) * 100, 100) : 0;

                        fill.style.width = `${progress}%`;
                        time.textContent = audio.paused || current === 0
                            ? formatTime(total)
                            : `${formatTime(current)} / ${formatTime(total)}`;
                    };

                    button.addEventListener('click', async () => {
                        if (audio.paused) {
                            await audio.play();
                            return;
                        }

                        audio.pause();
                    });

                    track.addEventListener('click', event => {
                        const total = Number.isFinite(audio.duration) ? audio.duration : 0;
                        if (!total) {
                            return;
                        }

                        const rect = track.getBoundingClientRect();
                        audio.currentTime = ((event.clientX - rect.left) / rect.width) * total;
                    });

                    audio.addEventListener('play', () => {
                        button.textContent = 'Ⅱ';
                        button.setAttribute('aria-label', 'Pause voice note');
                    });

                    audio.addEventListener('pause', () => {
                        button.textContent = '▶';
                        button.setAttribute('aria-label', 'Play voice note');
                    });

                    audio.addEventListener('ended', () => {
                        button.textContent = '▶';
                        updateTime();
                    });

                    audio.addEventListener('loadedmetadata', updateTime);
                    audio.addEventListener('timeupdate', updateTime);
                    player.dataset.voiceReady = 'true';
                    updateTime();
                };

                const createVoicePlayer = (url, duration = null) => {
                    const player = document.createElement('div');
                    player.className = 'voice-player';
                    player.dataset.voicePlayer = 'true';
                    if (duration) {
                        player.dataset.duration = String(duration);
                    }

                    const audio = document.createElement('audio');
                    audio.src = url;
                    audio.preload = 'metadata';
                    audio.className = 'voice-audio';

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'voice-play';
                    button.setAttribute('aria-label', 'Play voice note');
                    button.textContent = '▶';

                    const track = document.createElement('button');
                    track.type = 'button';
                    track.className = 'voice-track';
                    track.setAttribute('aria-label', 'Seek voice note');

                    const fill = document.createElement('span');
                    fill.className = 'voice-progress';

                    const bars = document.createElement('span');
                    bars.className = 'voice-bars';

                    for (let index = 0; index < 18; index += 1) {
                        const bar = document.createElement('span');
                        bar.style.setProperty('--bar-height', `${8 + ((index * 7) % 18)}px`);
                        bars.appendChild(bar);
                    }

                    const time = document.createElement('span');
                    time.className = 'voice-time';

                    track.appendChild(fill);
                    track.appendChild(bars);
                    player.appendChild(audio);
                    player.appendChild(button);
                    player.appendChild(track);
                    player.appendChild(time);
                    initializeVoicePlayer(player);

                    return player;
                };

                document.querySelectorAll('[data-voice-player]').forEach(initializeVoicePlayer);

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

                    if (attachment.type === 'audio') {
                        wrapper.appendChild(createVoicePlayer(url, attachment.duration));

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

                    if (String(event.conversation_id) !== String(conversationId)) {
                        return;
                    }

                    updateConversationPreview(event);

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
                    row.dataset.createdAt = event.created_at || '';

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
                        setOpenConversationUnread(0);
                    } else if (sender === 'visitor') {
                        setOpenConversationUnread(pendingOpenConversationMessages + 1);
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
                    cancelRecording();

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
                const messageForm = document.querySelector('[data-agent-message-form]');
                const micButton = document.querySelector('[data-agent-mic]');
                const attachButton = document.querySelector('[data-agent-attach]');
                const attachmentInput = document.querySelector('[data-agent-attachment]');
                const attachmentRow = document.querySelector('[data-agent-attachment-row]');
                const attachmentName = document.querySelector('[data-agent-attachment-name]');
                const attachmentClear = document.querySelector('[data-agent-attachment-clear]');
                const durationInput = document.querySelector('[data-agent-attachment-duration]');
                const recordingArea = document.querySelector('[data-agent-recording-area]');
                const recordingStatus = document.querySelector('[data-agent-recording-status]');
                const recordingCancel = document.querySelector('[data-agent-recording-cancel]');
                const recordingSend = document.querySelector('[data-agent-recording-send]');
                let mediaRecorder = null;
                let mediaStream = null;
                let recordingBlob = null;
                let recordingMimeType = null;
                let recordingStartedAt = null;
                let recordingSeconds = 0;
                let recordingTimer = null;
                let recordingCancelled = false;

                const showSelectedAttachment = () => {
                    const file = attachmentInput?.files?.[0];

                    if (!attachmentRow || !attachmentName) {
                        return;
                    }

                    if (!file) {
                        attachmentRow.hidden = true;
                        attachmentName.textContent = '';
                        return;
                    }

                    attachmentName.textContent = file.name;
                    attachmentRow.hidden = false;
                };

                const clearSelectedAttachment = () => {
                    if (attachmentInput) {
                        attachmentInput.value = '';
                    }

                    if (durationInput) {
                        durationInput.value = '';
                    }

                    showSelectedAttachment();
                };

                const voiceMimeType = () => {
                    if (!window.MediaRecorder?.isTypeSupported) {
                        return '';
                    }

                    return [
                        'audio/webm;codecs=opus',
                        'audio/webm',
                        'audio/ogg',
                        'audio/mp4',
                        'audio/m4a',
                    ].find(type => MediaRecorder.isTypeSupported(type)) || '';
                };

                const voiceExtension = mimeType => {
                    if (mimeType.includes('ogg')) {
                        return 'ogg';
                    }

                    if (mimeType.includes('mp4') || mimeType.includes('m4a')) {
                        return 'm4a';
                    }

                    return 'webm';
                };

                const stopVoiceStream = () => {
                    mediaStream?.getTracks().forEach(track => track.stop());
                    mediaStream = null;
                };

                const updateRecordingTimer = () => {
                    const minutes = String(Math.floor(recordingSeconds / 60)).padStart(2, '0');
                    const seconds = String(recordingSeconds % 60).padStart(2, '0');

                    if (recordingStatus) {
                        recordingStatus.textContent = `Recording ${minutes}:${seconds}`;
                    }
                };

                const showRecordingUi = () => {
                    if (recordingArea) {
                        recordingArea.hidden = false;
                    }

                    if (textarea) {
                        textarea.disabled = true;
                    }

                    if (micButton) {
                        micButton.disabled = true;
                    }

                    updateRecordingTimer();
                };

                const hideRecordingUi = () => {
                    if (recordingArea) {
                        recordingArea.hidden = true;
                    }

                    if (textarea) {
                        textarea.disabled = false;
                    }

                    if (micButton) {
                        micButton.disabled = false;
                    }
                };

                const stopRecording = () => {
                    if (recordingTimer) {
                        clearInterval(recordingTimer);
                        recordingTimer = null;
                    }

                    if (mediaRecorder?.state === 'recording') {
                        return new Promise(resolve => {
                            mediaRecorder.addEventListener('stop', resolve, { once: true });
                            mediaRecorder.stop();
                        });
                    }

                    if (mediaRecorder?.state === 'inactive') {
                        return Promise.resolve();
                    }

                    stopVoiceStream();
                    return Promise.resolve();
                };

                const cancelRecording = () => {
                    recordingCancelled = true;
                    stopRecording();
                    recordingBlob = null;
                    mediaRecorder = null;
                    hideRecordingUi();
                };

                const startRecording = async () => {
                    if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
                        alert('Voice recording is not supported in this browser.');
                        return;
                    }

                    try {
                        mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        const mimeType = voiceMimeType();
                        const chunks = [];

                        mediaRecorder = new MediaRecorder(mediaStream, mimeType ? { mimeType } : undefined);
                        recordingMimeType = mediaRecorder.mimeType || mimeType || 'audio/webm';
                        recordingBlob = null;
                        recordingCancelled = false;
                        recordingSeconds = 0;
                        recordingStartedAt = Date.now();

                        mediaRecorder.addEventListener('dataavailable', event => {
                            if (event.data?.size) {
                                chunks.push(event.data);
                            }
                        });

                        mediaRecorder.addEventListener('stop', () => {
                            recordingBlob = !recordingCancelled && chunks.length
                                ? new Blob(chunks, { type: recordingMimeType })
                                : null;
                            stopVoiceStream();
                        });

                        mediaRecorder.start();
                        showRecordingUi();

                        recordingTimer = setInterval(() => {
                            recordingSeconds = Math.floor((Date.now() - recordingStartedAt) / 1000);
                            updateRecordingTimer();

                            if (recordingSeconds >= 120) {
                                stopRecording();
                            }
                        }, 250);
                    } catch (error) {
                        stopVoiceStream();
                        alert('Microphone access is required to record a voice note.');
                    }
                };

                const sendRecording = async () => {
                    await stopRecording();

                    if (!recordingBlob) {
                        hideRecordingUi();
                        return;
                    }

                    if (recordingBlob.size > 10 * 1024 * 1024) {
                        recordingBlob = null;
                        hideRecordingUi();
                        alert('Voice note must be under 10 MB.');
                        return;
                    }

                    const file = new File(
                        [recordingBlob],
                        `voice-note.${voiceExtension(recordingMimeType)}`,
                        { type: recordingMimeType }
                    );
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    attachmentInput.files = dataTransfer.files;

                    if (durationInput) {
                        durationInput.value = String(Math.min(recordingSeconds, 120));
                    }

                    recordingBlob = null;
                    recordingCancelled = false;
                    mediaRecorder = null;
                    hideRecordingUi();
                    showSelectedAttachment();
                    messageForm?.requestSubmit();
                };

                messageForm?.addEventListener('submit', async event => {
                    const textValue = textarea?.value.trim() || '';
                    const hasAttachment = !!attachmentInput?.files?.length;

                    if (!textValue && !hasAttachment) {
                        return;
                    }

                    event.preventDefault();

                    if (messageForm.dataset.sending === 'true') {
                        return;
                    }

                    messageForm.dataset.sending = 'true';
                    messageForm.querySelectorAll('button[type="submit"]').forEach(button => {
                        button.disabled = true;
                    });

                    let sentSuccessfully = false;

                    try {
                        const response = await fetch(messageForm.action, {
                            method: 'POST',
                            body: new FormData(messageForm),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(data.message || 'Message could not be sent.');
                        }

                        sentSuccessfully = true;

                        if (data.chat_message) {
                            appendMessage(data.chat_message);
                        }
                    } catch (error) {
                        alert(error.message || 'Message could not be sent.');
                    } finally {
                        messageForm.dataset.sending = 'false';
                        messageForm.querySelectorAll('button[type="submit"]').forEach(button => {
                            button.disabled = false;
                        });
                    }

                    if (!sentSuccessfully) {
                        return;
                    }

                    try {
                        if (textarea) {
                            textarea.value = '';
                        }

                        if (attachmentInput) {
                            attachmentInput.value = '';
                        }

                        if (durationInput) {
                            durationInput.value = '';
                        }

                        showSelectedAttachment();
                    } catch (renderError) {
                        console.warn('Message sent, but composer cleanup failed.', renderError);
                    }
                });

                if (micButton && (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder)) {
                    micButton.hidden = true;
                }

                attachButton?.addEventListener('click', () => attachmentInput?.click());
                attachmentInput?.addEventListener('change', showSelectedAttachment);
                attachmentClear?.addEventListener('click', clearSelectedAttachment);
                micButton?.addEventListener('click', startRecording);
                recordingCancel?.addEventListener('click', cancelRecording);
                recordingSend?.addEventListener('click', sendRecording);

                if (textarea) {
                    textarea.addEventListener('keydown', event => {
                        if (event.key !== 'Enter' || event.shiftKey) {
                            return;
                        }

                        event.preventDefault();

                        if (!textarea.value.trim() && !attachmentInput?.files?.length) {
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
