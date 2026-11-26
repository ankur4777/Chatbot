<style>
/*
 * Super Admin panel visual theme only.
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
    font-family: var(--shabdd-font-sans);
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
.fi-sidebar .fi-logo { color: #fff !important; font-weight: 700; letter-spacing: -.03em; }
.fi-sidebar .fi-sidebar-nav { padding: 1rem .75rem; }
.fi-sidebar .fi-sidebar-group-btn { color: #aab2c4 !important; }
.fi-sidebar .fi-sidebar-group-label { color: #aab2c4 !important; font-size: .75rem; font-weight: 700; letter-spacing: .065em; }
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

/* Light utility header and clear Filament table/form surfaces on every admin page. */
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


/* Admin-specific overview; business metrics use actual model records. */
.aw-page { color: var(--cw-ink); padding-bottom: 14px; }
.aw-page * { box-sizing: border-box; }
.aw-header { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
.aw-eyebrow, .aw-section-label { color: var(--cw-red-dark); font-size: .75rem; font-weight: 700; letter-spacing: .085em; }
.aw-header h1 { margin: 8px 0 9px; font-size: clamp(1.7rem,2.2vw,2.25rem); line-height: 1.17; letter-spacing: -.025em; font-weight: 700; color: var(--cw-ink); }
.aw-header p:last-child { color: var(--cw-muted); margin: 0; font-size: .9rem; line-height: 1.55; }
.aw-header-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.aw-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; border-radius: 10px; padding: 10px 16px; font-weight: 600; font-size: .82rem; white-space: nowrap; text-decoration: none; border: 1px solid transparent; transition: background .15s ease, border-color .15s ease; }
.aw-btn-primary { background: var(--cw-red); color: #fff !important; }
.aw-btn-primary:hover { background: var(--cw-red-dark); }
.aw-btn-light { color: var(--cw-ink) !important; border-color: var(--cw-line); background: #fff; }
.aw-btn-light:hover { background: #fff5f6; }
.aw-stats { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 14px; margin-bottom: 19px; }
.aw-stat { min-width: 0; display: block; background: #fff; color: var(--cw-ink) !important; border: 1px solid var(--cw-line); border-radius: 15px; box-shadow: 0 3px 15px rgba(26,35,54,.035); padding: 21px; text-decoration: none; transition: border-color .15s ease; }
.aw-stat:hover { border-color: #f5bbc0; }
.aw-stat-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.aw-stat-top > span:first-child { font-size: .83rem; font-weight: 600; color: #5c6679; }
.aw-stat-icon { display: grid; place-items: center; flex-shrink: 0; width: 37px; height: 37px; border-radius: 11px; color: var(--cw-red-dark); background: var(--cw-red-soft); font-size: 1.15rem; }
.aw-stat strong { display: block; margin: 16px 0 10px; line-height: 1; font-size: clamp(1.65rem,2.25vw,2.1rem); font-weight: 700; letter-spacing: -.025em; }
.aw-stat small { display: block; color: #8791a0; font-size: .75rem; }
.aw-columns { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); align-items: start; gap: 16px; margin-bottom: 16px; }
.aw-panel { min-width: 0; background: #fff; border: 1px solid var(--cw-line); border-radius: 15px; padding: 23px; box-shadow: 0 3px 15px rgba(26,35,54,.035); }
.aw-panel-heading { display: flex; align-items: end; justify-content: space-between; gap: 10px; margin-bottom: 19px; }
.aw-panel-heading h2 { color: var(--cw-ink); font-size: 1.08rem; font-weight: 700; letter-spacing: -.025em; margin: 5px 0 0; }
.aw-panel-heading a { font-size: .79rem; text-decoration: none; font-weight: 600; color: var(--cw-red-dark); white-space: nowrap; }
.aw-panel-heading a:hover { text-decoration: underline; }
.aw-list-row { display: flex; align-items: center; gap: 12px; border-top: 1px solid #eef0f5; padding: 15px 0; min-width: 0; }
.aw-row-icon { display: grid; place-items: center; flex-shrink: 0; width: 40px; height: 40px; border-radius: 11px; background: #fff0f1; color: #cf434b; font-size: .95rem; font-weight: 700; }
.aw-row-main { min-width: 0; flex: 1; }
.aw-row-main strong { display: block; font-size: .83rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--cw-ink); }
.aw-row-main small { display: block; margin-top: 4px; font-size: .75rem; color: #8791a0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.aw-pill { border-radius: 999px; padding: 6px 10px; font-size: .75rem; font-weight: 600; white-space: nowrap; }
.aw-pill-success { background: #edf8f1; color: #168451; }
.aw-pill-muted { background: #f4f5f7; color: #6d7788; }
.aw-empty { display: grid; justify-items: center; gap: 5px; text-align: center; padding: 37px 10px; color: #8791a0; font-size: .79rem; }
.aw-empty strong { color: var(--cw-ink); font-size: .87rem; }
.aw-footnote { font-size: .75rem; color: #8791a0; padding-top: 13px; border-top: 1px solid #eef0f5; margin: 0; }
.aw-shortcuts { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 12px; }
.aw-shortcuts > a { min-width: 0; display: flex; align-items: center; gap: 10px; padding: 13px; border: 1px solid var(--cw-line); border-radius: 12px; text-decoration: none; color: var(--cw-ink) !important; background: #fff; }
.aw-shortcuts > a:hover { background: #fffafb; border-color: #f4c9cd; }
.aw-shortcut-icon { display: grid; place-items: center; flex-shrink: 0; width: 37px; height: 37px; color: var(--cw-red-dark); background: var(--cw-red-soft); border-radius: 10px; font-size: 1.05rem; }
.aw-shortcuts > a > span:nth-child(2) { min-width: 0; flex: 1; }
.aw-shortcuts strong, .aw-shortcuts small { display: block; }
.aw-shortcuts strong { font-size: .81rem; font-weight: 600; }
.aw-shortcuts small { font-size: .75rem; color: #8791a0; margin-top: 3px; }
.aw-shortcuts b { font-size: .85rem; color: #8b95a4; font-weight: 600; }
.aw-note { font-size: .75rem; margin-top: 19px; color: #8791a0; }
@media (max-width: 1280px) { .aw-stats { grid-template-columns: repeat(2,minmax(0,1fr)); } .aw-shortcuts { grid-template-columns: repeat(2,minmax(0,1fr)); } }
@media (max-width: 950px) { .aw-header { flex-direction: column; align-items: flex-start; } .aw-columns { grid-template-columns: 1fr; } }
@media (max-width: 560px) { .aw-header-actions { width: 100%; } .aw-btn { flex: 1; } .aw-panel { padding: 18px; } .aw-stat { padding: 17px; } .aw-stats { gap: 10px; } .aw-stat strong { font-size: 1.7rem; } .aw-shortcuts { grid-template-columns: 1fr; } }
@media (prefers-reduced-motion: reduce) { .aw-btn { transition: none; } }

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
</style>
