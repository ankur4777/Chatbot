<style>
/*
 * Client / Owner panel visual theme only.
 * Filament pages, Livewire forms, permissions and database logic are unchanged.
 */
:root {
    --cw-red: #fa4d55;
    --cw-red-dark: #e33d46;
    --cw-red-soft: #fff0f1;
    --cw-ink: #20283b;
    --cw-muted: #687386;
    --cw-line: #e8eaf0;
    --cw-bg: #f8f9fc;
    --cw-sidebar: #171a28;
    --cw-sidebar-deep: #242738;
}

html, body, .fi-body, .fi-layout, .fi-main-ctn, .fi-main {
    background: var(--cw-bg) !important;
    color: var(--cw-ink);
    font-family: Inter, 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
}
.fi-main { max-width: 1600px; }
.fi-page-header-heading, .fi-header-heading { color: var(--cw-ink); letter-spacing: -.025em; }
.fi-page-header-subheading { color: var(--cw-muted); }

/* Dark navigation remains legible independently of browser theme settings. */
.fi-sidebar, .fi-sidebar-header, .fi-sidebar-footer {
    background: var(--cw-sidebar) !important;
    color: #fff !important;
    border-color: rgba(255,255,255,.09) !important;
}
.fi-sidebar { background: linear-gradient(170deg, var(--cw-sidebar), var(--cw-sidebar-deep)) !important; border-right: 0 !important; }
.fi-sidebar .fi-logo { color: #fff !important; font-weight: 800; letter-spacing: -.03em; }
.fi-sidebar .fi-sidebar-nav { padding: 1rem .75rem; }
.fi-sidebar .fi-sidebar-group-btn { color: #aab2c4 !important; }
.fi-sidebar .fi-sidebar-group-label { color: #aab2c4 !important; font-size: .72rem; font-weight: 700; letter-spacing: .065em; }
.fi-sidebar .fi-sidebar-group-collapse-btn { color: #aab2c4 !important; }
.fi-sidebar .fi-sidebar-item-btn {
    border-radius: 11px !important;
    color: #e4e7ef !important;
    background: transparent;
    padding: .73rem .85rem;
    transition: background .15s ease, color .15s ease;
}
.fi-sidebar .fi-sidebar-item-label, .fi-sidebar .fi-sidebar-item-icon { color: inherit !important; }
.fi-sidebar .fi-sidebar-item-btn:hover, .fi-sidebar .fi-sidebar-item-btn:focus-visible {
    background: rgba(255,255,255,.09) !important;
    color: #fff !important;
}
.fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
    background: linear-gradient(100deg, #ff6268, #e9434b) !important;
    color: #fff !important;
    box-shadow: 0 5px 16px rgba(228,67,75,.21);
}
.fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-icon,
.fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-label { color: #fff !important; }

/* Light utility header and clear Filament table/form surfaces on every client page. */
.fi-topbar { background: #fff !important; color: var(--cw-ink) !important; border-bottom: 1px solid var(--cw-line) !important; box-shadow: 0 1px 4px rgba(24,32,50,.025) !important; }
.fi-topbar .fi-logo { color: var(--cw-ink) !important; }
.fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-modal-window {
    background: #fff !important;
    border: 1px solid var(--cw-line) !important;
    border-radius: 15px !important;
    box-shadow: 0 3px 15px rgba(26,35,54,.035) !important;
    color: var(--cw-ink);
}
.fi-section-header-heading, .fi-section-content, .fi-ta-header-heading { color: var(--cw-ink); }
.fi-ta-ctn .fi-ta-header, .fi-ta-ctn .fi-ta-header-cell { background: #fff !important; }
.fi-ta-ctn .fi-ta-row, .fi-ta-ctn .fi-ta-cell { background: #fff !important; }
.fi-ta-ctn .fi-ta-row:hover .fi-ta-cell { background: #fff9f9 !important; }
.fi-ta-ctn .fi-ta-header-cell { border-bottom-color: var(--cw-line) !important; }
.fi-ta-ctn .fi-ta-header-cell-label, .fi-ta-ctn .fi-ta-text, .fi-ta-ctn .fi-ta-cell { color: var(--cw-ink); }
.fi-btn, .fi-input-wrp { border-radius: 10px !important; }
.fi-input-wrp { background: #fff; }
.fi-ta-empty-state-heading { color: var(--cw-ink); }
.fi-ta-empty-state-description { color: var(--cw-muted); }

/* Overview: simpler than stock Filament, using real company-scoped metrics. */
.cw-page { color: var(--cw-ink); padding-bottom: 12px; }
.cw-page * { box-sizing: border-box; }
.cw-header { display: flex; justify-content: space-between; align-items: center; gap: 22px; margin: 0 0 22px; }
.cw-eyebrow { font-size: .75rem; font-weight: 750; letter-spacing: .055em; color: var(--cw-red-dark); margin-bottom: 9px; }
.cw-header h1 { margin: 0; font-size: clamp(1.7rem,2.3vw,2.25rem); font-weight: 800; color: var(--cw-ink); letter-spacing: -.045em; line-height: 1.14; }
.cw-header p:last-child { margin-top: 9px; max-width: 620px; color: var(--cw-muted); font-size: .9rem; line-height: 1.55; }
.cw-header-actions { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
.cw-button { display: inline-flex; align-items: center; justify-content: center; padding: 11px 15px; border-radius: 10px; text-decoration: none; font-weight: 720; font-size: .82rem; transition: background .15s ease, transform .15s ease; white-space: nowrap; }
.cw-button:hover { transform: translateY(-1px); }
.cw-button-primary { color: #fff !important; background: var(--cw-red); }
.cw-button-primary:hover { background: var(--cw-red-dark); }
.cw-button-secondary { color: var(--cw-ink) !important; background: #fff; border: 1px solid var(--cw-line); }
.cw-button-secondary:hover { background: #fff7f7; }
.cw-stats { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 14px; margin-bottom: 20px; }
.cw-stat { display: block; background: #fff; color: var(--cw-ink) !important; border: 1px solid var(--cw-line); border-radius: 15px; text-decoration: none; padding: 21px; min-width: 0; box-shadow: 0 3px 15px rgba(26,35,54,.035); }
.cw-stat:hover { border-color: #f7b9bd; }
.cw-stat-top { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.cw-stat-label { font-size: .81rem; font-weight: 670; color: #566174; }
.cw-stat-icon { display: grid; place-items: center; flex-shrink: 0; width: 35px; height: 35px; background: var(--cw-red-soft); color: var(--cw-red-dark); border-radius: 10px; font-size: 1.12rem; }
.cw-stat-value { display: block; margin: 13px 0 11px; font-size: clamp(1.75rem,2.3vw,2.1rem); font-weight: 790; letter-spacing: -.045em; line-height: 1; }
.cw-stat-meta { font-size: .75rem; color: #828d9c; }
.cw-stat-meta strong { color: #129267; font-weight: 750; }
.cw-content-grid { display: grid; grid-template-columns: minmax(0,1.45fr) minmax(290px,1fr); align-items: start; gap: 16px; margin-bottom: 16px; }
.cw-panel { background: #fff; border: 1px solid var(--cw-line); border-radius: 15px; padding: 22px; min-width: 0; box-shadow: 0 3px 15px rgba(26,35,54,.035); }
.cw-panel-title { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin: 0 0 17px; }
.cw-panel-title h2 { margin: 0; color: var(--cw-ink); font-size: 1.05rem; font-weight: 770; letter-spacing: -.025em; }
.cw-panel-title a { text-decoration: none; color: var(--cw-red-dark); font-size: .8rem; font-weight: 710; white-space: nowrap; }
.cw-panel-title a:hover { text-decoration: underline; }
.cw-activity, .cw-lead { display: flex; gap: 12px; align-items: center; padding: 14px 0; border-top: 1px solid #f0f1f5; }
.cw-avatar { display: grid; place-items: center; flex-shrink: 0; width: 39px; height: 39px; background: #fff0f1; color: var(--cw-red-dark); border-radius: 11px; font-weight: 800; }
.cw-activity-copy, .cw-lead-copy { flex: 1; min-width: 0; }
.cw-activity-copy strong, .cw-lead-copy strong { display: block; font-size: .84rem; font-weight: 730; color: var(--cw-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cw-activity-copy small, .cw-lead-copy small { display: block; margin-top: 4px; color: #828d9c; font-size: .73rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cw-status { font-size: .69rem; font-weight: 690; padding: 6px 9px; border-radius: 30px; background: #fff0f1; color: #c63d47; white-space: nowrap; }
.cw-side-stack { display: grid; gap: 16px; }
.cw-lead { justify-content: space-between; }
.cw-lead time { color: #8a95a5; font-size: .71rem; white-space: nowrap; }
.cw-shortcut { display: flex; gap: 12px; align-items: center; color: var(--cw-ink) !important; text-decoration: none; padding: 12px 0; border-top: 1px solid #f0f1f5; }
.cw-shortcut:hover .cw-shortcut-title { color: var(--cw-red-dark); }
.cw-shortcut-icon { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 10px; background: var(--cw-red-soft); color: var(--cw-red-dark); font-size: 1.1rem; flex-shrink: 0; }
.cw-shortcut-title { display: block; font-size: .83rem; font-weight: 700; }
.cw-shortcut-subtitle { display: block; color: #8791a1; font-size: .72rem; margin-top: 3px; }
.cw-shortcut-arrow { margin-left: auto; color: #8b94a4; font-size: .85rem; }
.cw-empty { padding: 28px 10px; text-align: center; color: #818a9a; font-size: .82rem; }
.cw-empty strong { display: block; color: var(--cw-ink); margin-bottom: 5px; }
.cw-note { margin-top: 18px; color: #8590a0; font-size: .73rem; }

@media (max-width: 1250px) { .cw-stats { grid-template-columns: repeat(2,minmax(0,1fr)); } .cw-content-grid { grid-template-columns: 1fr; } .cw-side-stack { grid-template-columns: repeat(2,minmax(0,1fr)); } }
@media (max-width: 760px) { .cw-header { flex-direction: column; align-items: flex-start; } .cw-side-stack { grid-template-columns: 1fr; } }
@media (max-width: 500px) { .cw-stats { gap: 10px; } .cw-stat { padding: 16px; } .cw-panel { padding: 18px; } .cw-header-actions { width: 100%; } .cw-button { flex: 1; } .cw-status { max-width: 90px; overflow: hidden; text-overflow: ellipsis; } }
@media (prefers-reduced-motion: reduce) { .cw-button, .fi-sidebar .fi-sidebar-item-btn { transition: none !important; } }

/* The attribution is the final item INSIDE the scrollable sidebar navigation.
   It is deliberately not fixed or sticky to the viewport. */
.fi-main-sidebar .fi-sidebar-nav .cw-powered-footer {
    display: block;
    width: 100%;
    position: static;
    flex: none;
    margin: 18px 0 0;
    padding: 16px 14px 20px;
    border-top: 1px solid rgba(255,255,255,.10);
    background: transparent;
}
.fi-main-sidebar .cw-powered-card {
    background: transparent;
    border: 0;
    border-radius: 0;
    padding: 0;
    text-align: left;
    box-shadow: none;
}
.fi-main-sidebar .cw-powered-label {
    display: block;
    margin-bottom: 6px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .055em;
    line-height: 1.35;
    color: rgba(255,255,255,.70);
}
.fi-main-sidebar .cw-powered-card img {
    display: block;
    width: 130px;
    max-width: 100%;
    height: auto;
    margin: 0;
    object-fit: contain;
    background: transparent;
    box-shadow: none;
}
@media (max-width: 640px) {
    .fi-main-sidebar .fi-sidebar-nav .cw-powered-footer { padding: 14px 12px 18px; }
}

.cw-subscription { background:#fff; border:1px solid var(--cw-line); border-radius:15px; padding:22px; margin-bottom:20px; }
.cw-subscription-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
.cw-subscription-head h2,.cw-subscription-warning h2 { font-size:1.12rem; font-weight:770; color:var(--cw-ink); }
.cw-subscription-head p { color:var(--cw-muted); font-size:.8rem; margin-top:4px; }
.cw-subscription-badge { border-radius:99px; padding:6px 12px; font-weight:700; font-size:.76rem; background:#fff0f1; color:#b4232e; }
.cw-subscription-active { background:#e9f7ef; color:#167044; }
.cw-subscription-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
.cw-subscription-grid > div { padding:14px; background:#f8f9fc; border-radius:10px; min-width:0; }
.cw-subscription-grid span,.cw-subscription-grid small { display:block; color:var(--cw-muted); font-size:.78rem; }
.cw-subscription-grid strong { display:block; font-size:1rem; font-weight:740; margin-top:7px; overflow-wrap:anywhere; }
.cw-subscription-grid .cw-subscription-count { color:var(--cw-red-dark); font-size:1.4rem; }
.cw-subscription-grid small { margin-top:5px; }
.cw-subscription-warning { background:#fff; border:1px solid #f3c9cc; border-radius:15px; padding:24px; }
.cw-subscription-warning p,.cw-subscription-dialog p { color:var(--cw-muted); line-height:1.6; margin:10px 0 18px; font-size:.9rem; }
.cw-subscription-dialog { border:1px solid var(--cw-line); border-radius:18px; padding:0; max-width:min(440px,calc(100% - 32px)); width:100%; margin:auto; background:#fff; color:var(--cw-ink); box-shadow:0 18px 70px #11182735; }
.cw-subscription-dialog::backdrop { background:#171a28a0; }
.cw-subscription-dialog-content { padding:30px; text-align:center; }
.cw-subscription-dialog h2 { font-size:1.35rem; font-weight:800; }
.cw-subscription-symbol { display:grid; place-items:center; width:52px; height:52px; background:#fff0f1; color:#cf3541; border-radius:50%; margin:0 auto 18px; font-size:1.6rem; font-weight:800; }
.cw-subscription-dialog button { width:100%; }
body:has(.cw-subscription-locked) .fi-sidebar-nav .fi-sidebar-group { display:none !important; }
body:has(.cw-subscription-locked) .fi-global-search { display:none !important; }
@media (max-width:760px) { .cw-subscription-grid { grid-template-columns:1fr; } .cw-subscription { padding:17px; } }

/* Live Inbox monitoring dashboard */
.cw-inbox { display:grid; gap:18px; color:var(--cw-ink); }
.cw-inbox-header { display:flex; justify-content:space-between; align-items:end; gap:18px; }
.cw-inbox-header h2,.cw-inbox-panel-head h2 { font-size:1.14rem; font-weight:760; color:var(--cw-ink); margin:0; }
.cw-inbox-header p,.cw-inbox-panel-head p { color:var(--cw-muted); font-size:.84rem; margin:5px 0 0; }
.cw-inbox-header small { color:var(--cw-muted); font-size:.72rem; display:block; margin-top:6px; }
.cw-inbox-select { display:grid; gap:6px; min-width:220px; }
.cw-inbox-select label { color:var(--cw-ink); font-weight:650; font-size:.8rem; }
.cw-inbox-select select { width:100%; background:#fff; color:var(--cw-ink); border:1px solid var(--cw-line); border-radius:10px; padding:10px 30px 10px 12px; font-size:.85rem; }
.cw-inbox-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
.cw-inbox-stat,.cw-inbox-panel { min-width:0; background:#fff; border:1px solid var(--cw-line); border-radius:15px; padding:20px; box-shadow:0 3px 15px rgba(26,35,54,.035); color:var(--cw-ink); }
a.cw-inbox-stat:hover { border-color:var(--cw-red); }
.cw-inbox-stat span,.cw-inbox-stat small { display:block; color:var(--cw-muted); font-size:.8rem; }
.cw-inbox-stat strong { display:block; font-size:clamp(1.45rem,2.3vw,2rem); font-weight:780; margin:9px 0; letter-spacing:-.03em; color:var(--cw-red-dark); }
.cw-inbox-stat strong.cw-inbox-green { color:#167044; }
.cw-inbox-panel-head { display:flex; justify-content:space-between; align-items:center; gap:12px; color:var(--cw-muted); font-size:.8rem; }
.cw-inbox-table-wrap { overflow:auto; margin-top:15px; }
.cw-inbox-table { width:100%; border-collapse:collapse; text-align:left; font-size:.85rem; }
.cw-inbox-table th,.cw-inbox-table td { padding:13px 10px; border-bottom:1px solid var(--cw-line); white-space:nowrap; }
.cw-inbox-table th { font-weight:650; color:var(--cw-muted); background:#f8f9fc; }
.cw-inbox-table a { color:var(--cw-ink); font-weight:650; }
.cw-inbox-table a:hover { color:var(--cw-red-dark); text-decoration:underline; }
.cw-inbox-status { border-radius:99px; padding:4px 10px; font-size:.74rem; font-weight:650; background:#f1f3f6; color:#536072; }
.cw-inbox-status.cw-inbox-online { background:#e9f7ef; color:#167044; }
.cw-inbox-status.cw-inbox-away { background:#fff4e5; color:#8b5800; }
.cw-inbox-bar { height:7px; min-width:85px; max-width:180px; border-radius:99px; background:#f1f3f6; overflow:hidden; }
.cw-inbox-bar span { display:block; height:100%; border-radius:99px; background:var(--cw-red); }
.cw-inbox-empty,.cw-inbox-footnote { color:var(--cw-muted); font-size:.8rem; padding:14px 0; }
@media(max-width:1050px) { .cw-inbox-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:650px) { .cw-inbox-header { flex-direction:column; align-items:stretch; } .cw-inbox-select { min-width:0; } .cw-inbox-grid { grid-template-columns:1fr; } .cw-inbox-stat,.cw-inbox-panel { padding:16px; } }
</style>
