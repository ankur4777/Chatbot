<style>
/* Client-panel-only stylesheet. No global admin or agent changes. */
.fi-layout { --client-accent:#e9493e; --client-ink:#1c293c; --client-line:#e7ebf1; font-family: 'Inter','Instrument Sans',ui-sans-serif,system-ui,sans-serif; }
.fi-layout .fi-sidebar { background:#fff; border-right:1px solid #e8ecf2; }
.fi-layout .fi-sidebar-nav { padding-inline:.75rem; }
.fi-layout .fi-sidebar-item-button { border-radius:10px; transition:background .18s ease; }
.fi-layout .fi-sidebar-item-button:hover { background:#fff3f1; }
.fi-layout .fi-sidebar-item.fi-active > .fi-sidebar-item-button { background:#fff0ee; color:#c53d35; }
.fi-layout .fi-sidebar-group-label { letter-spacing:.085em; font-size:.7rem; font-weight:700; }
.fi-layout .fi-topbar { background:rgba(255,255,255,.97); border-bottom:1px solid #e8ecf2; }
.fi-layout .fi-main { max-width:1600px; }
.fi-layout .fi-section,.fi-layout .fi-ta-ctn { border-radius:16px; }
.fi-layout .fi-btn { border-radius:10px; }

.client-overview { --c-text:#202b40; --c-muted:#748094; --c-line:#e7ebf1; --c-red:#e9493e; color:var(--c-text); padding-bottom:24px; }
.client-overview *, .client-overview *::before,.client-overview *::after { box-sizing:border-box; }
.client-hero { display:flex; align-items:center; justify-content:space-between; gap:26px; padding:clamp(26px,3vw,42px); border-radius:20px; background:linear-gradient(117deg,#202b41,#172237 64%,#36435c); color:#fff; overflow:hidden; }
.client-eyebrow,.client-section-title small,.client-panel-heading small { font-size:.7rem; font-weight:800; letter-spacing:.13em; color:#d6dfe9; }
.client-hero h1 { margin:15px 0 12px; font-size:clamp(1.85rem,3.15vw,2.6rem); font-weight:780; line-height:1.17; letter-spacing:-.045em; color:#fff; }
.client-punct { color:#f17064; }
.client-hero p:not(.client-eyebrow) { color:#c5ccda; font-size:.91rem; line-height:1.7; max-width:550px; }
.client-hero-meta { display:flex; align-items:center; gap:10px; margin-top:23px; color:#e0e6ef; font-size:.78rem; }
.client-hero-meta > span:last-child { color:#9aa7ba; }
.client-dot { background:#60d69d; width:8px; height:8px; border-radius:50%; box-shadow:0 0 0 4px #60d69d22; }
.client-hero-actions { display:flex; flex-direction:column; align-items:stretch; gap:10px; min-width:188px; }
.client-button { display:inline-flex; justify-content:space-between; align-items:center; gap:16px; padding:12px 16px; border-radius:10px; font-size:.83rem; font-weight:700; text-decoration:none; transition:background .15s ease,transform .15s ease; }
.client-button:hover { transform:translateY(-1px); }
.client-button-primary { background:#ef554a; color:#fff; }
.client-button-primary:hover { background:#d94439; color:#fff; }
.client-button-secondary { color:#fff; background:#ffffff1b; border:1px solid #ffffff30; }
.client-button-secondary:hover { background:#ffffff2b; color:#fff; }
.client-section-title { display:flex; justify-content:space-between; align-items:end; gap:14px; margin:30px 0 16px; }
.client-section-title small,.client-panel-heading small { color:#d95349; }
.client-section-title h2,.client-panel-heading h2 { margin-top:5px; color:#1d293c; font-weight:760; letter-spacing:-.035em; }
.client-section-title h2 { font-size:1.37rem; }
.client-section-title > span { color:var(--c-muted); font-size:.78rem; }
.client-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
.client-stat { display:block; padding:22px 20px; min-width:0; background:#fff; border:1px solid var(--c-line); border-radius:15px; text-decoration:none; color:var(--c-text); transition:border-color .15s ease,transform .15s ease,box-shadow .15s ease; }
.client-stat:hover { color:var(--c-text); transform:translateY(-2px); border-color:#f0b7b0; box-shadow:0 10px 26px #202b4010; }
.client-stat > span { display:flex; align-items:center; justify-content:space-between; gap:8px; color:#627085; font-weight:650; font-size:.78rem; }
.client-stat > span b { display:grid; place-items:center; width:33px; height:33px; border-radius:10px; background:#fff1ee; color:#d95349; font-size:1.2rem; font-weight:600; }
.client-stat > strong { display:block; margin:18px 0 13px; font-weight:780; font-size:2rem; letter-spacing:-.05em; line-height:1; }
.client-stat > small { display:block; color:#8792a3; font-size:.74rem; }
.client-stat > small em { color:#159369; font-style:normal; font-weight:750; }
.client-dashboard-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(300px,1fr); gap:17px; align-items:start; margin-top:21px; }
.client-side-column { display:grid; gap:17px; min-width:0; }
.client-panel { background:#fff; border:1px solid var(--c-line); border-radius:16px; padding:24px; min-width:0; }
.client-panel-heading { display:flex; justify-content:space-between; align-items:end; gap:12px; margin-bottom:18px; }
.client-panel-heading h2 { font-size:1.16rem; }
.client-panel-heading a { color:#d34b41; font-weight:740; font-size:.78rem; text-decoration:none; white-space:nowrap; }
.client-panel-heading a:hover { text-decoration:underline; }
.client-activity-row { display:flex; align-items:center; gap:13px; padding:15px 0; border-top:1px solid #eef0f4; }
.client-avatar { display:grid; place-items:center; width:42px; height:42px; border-radius:12px; flex-shrink:0; background:#f3f1fb; color:#766aa9; font-weight:760; }
.client-activity-main { min-width:0; flex:1; }
.client-activity-main strong { display:block; font-size:.86rem; font-weight:730; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.client-activity-main small { display:block; margin-top:5px; color:#8490a2; font-size:.74rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.client-status-tag { background:#f2f4f7; border-radius:20px; padding:6px 10px; color:#68758a; font-size:.68rem; font-weight:700; white-space:nowrap; }
.client-shortcut { display:flex; align-items:center; gap:12px; border-top:1px solid #eef0f4; padding:14px 0; text-decoration:none; color:var(--c-text); }
.client-shortcut:hover { color:#d95349; }
.client-shortcut > span { display:grid; place-items:center; flex-shrink:0; width:40px; height:40px; border-radius:11px; background:#fff2ef; color:#d95349; font-size:1.3rem; }
.client-shortcut > div { display:grid; flex:1; min-width:0; gap:4px; }
.client-shortcut strong,.client-lead-row strong { font-size:.82rem; font-weight:730; }
.client-shortcut small,.client-lead-row small { color:#8995a5; font-size:.72rem; }
.client-shortcut > b { color:#99a3b1; font-size:.85rem; }
.client-lead-row { display:flex; justify-content:space-between; align-items:start; gap:12px; padding:14px 0; border-top:1px solid #eef0f4; }
.client-lead-row > div { display:grid; gap:5px; min-width:0; }
.client-lead-row strong { overflow-wrap:anywhere; }
.client-lead-row time { font-size:.72rem; color:#919aaa; white-space:nowrap; }
.client-empty { display:grid; justify-items:center; gap:9px; padding:45px 15px; color:#8490a2; font-size:.8rem; text-align:center; }
.client-empty strong { color:#1e293d; font-size:.9rem; }
.client-empty-small,.client-footer-note { color:#8996a6; font-size:.77rem; }
.client-empty-small { padding:14px 0 2px; }
.client-footer-note { margin-top:22px; }
.dark .client-stat,.dark .client-panel { background:#192536; border-color:#344255; color:#f4f7fa; }
.dark .client-section-title h2,.dark .client-panel-heading h2,.dark .client-activity-main strong,.dark .client-shortcut,.dark .client-empty strong { color:#f4f7fa; }
.dark .client-activity-row,.dark .client-shortcut,.dark .client-lead-row { border-color:#344255; }
@media(max-width:1200px) { .client-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } .client-dashboard-grid { grid-template-columns:1fr; } .client-side-column { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:700px) { .client-hero { flex-direction:column; align-items:stretch; padding:27px 22px; } .client-hero-actions { flex-direction:row; } .client-button { flex:1; } .client-side-column { grid-template-columns:1fr; } .client-panel { padding:19px; } }
@media(max-width:470px) { .client-stats { gap:10px; } .client-stat { padding:17px 13px; } .client-stat > strong { font-size:1.6rem; } .client-stat > span { font-size:.7rem; } .client-stat > span b { width:26px; height:26px; } .client-section-title > span { display:none; } .client-hero-actions { flex-direction:column; } }
@media(prefers-reduced-motion:reduce) { .client-overview a { transition:none !important; } }
</style>