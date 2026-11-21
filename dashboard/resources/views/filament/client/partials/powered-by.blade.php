<div
    class="cw-powered-footer"
    @if (filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop())
        x-show="$store.sidebar.isOpen"
    @endif
>
    <div class="cw-powered-card" aria-label="Powered by Shabdd Innovations">
        <span class="cw-powered-label">Powered by</span>
        <img
            src="{{ asset('images/shabdd-innovations-footer.png') }}"
            alt="Shabdd logo"
            width="130"
            height="38"
            loading="lazy"
            decoding="async"
        >
    </div>
</div>
