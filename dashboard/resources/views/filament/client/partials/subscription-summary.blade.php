<section class="cw-subscription" aria-labelledby="cw-subscription-heading">
    <div class="cw-subscription-head">
        <div>
            <h2 id="cw-subscription-heading">Your subscription</h2>
            <p>{{ $companyName }}</p>
        </div>
        <span class="cw-subscription-badge cw-subscription-{{ $subscriptionState }}">{{ match ($subscriptionState) {
            'active' => 'Active', 'expired' => 'Expired', 'scheduled' => 'Not started', default => 'Inactive'
        } }}</span>
    </div>
    <div class="cw-subscription-grid">
        <div><span>Subscription starts</span><strong>{{ $subscriptionStart ? \App\Support\BrowserTime::format($subscriptionStart) : 'Immediate' }}</strong></div>
        <div><span>Subscription expires</span><strong>{{ $subscriptionEnd ? \App\Support\BrowserTime::format($subscriptionEnd) : 'No expiry' }}</strong></div>
        <div><span>Time remaining</span>
            <strong class="cw-subscription-count">{{ $subscriptionState === 'expired' ? '0 days' : ($subscriptionState !== 'active' ? 'Not active' : ($subscriptionDays === null ? 'Unlimited' : ($subscriptionDays . ($subscriptionDays === 1 ? ' day' : ' days')))) }}</strong>
            @if ($subscriptionState === 'active' && $subscriptionDays !== null && $subscriptionDays <= 7)
                <small>Renew before expiry to avoid interruption.</small>
            @endif
        </div>
    </div>
</section>

@if ($subscriptionState !== 'active')
    <section class="cw-subscription-warning" role="status">
        <h2>{{ $subscriptionState === 'expired' ? 'Your subscription has ended' : 'Your subscription is not active yet' }}</h2>
        <p>Chatbot, live chat and all management features are unavailable. Your subscription must be active to use these features. Contact your administrator to renew or activate your subscription.</p>
        <button type="button" class="cw-button cw-button-primary" x-data x-on:click="$dispatch('cw-show-subscription-notice')">View subscription notice</button>
    </section>
    <dialog class="cw-subscription-dialog" aria-labelledby="cw-subscription-dialog-title"
        wire:key="subscription-notice-{{ $subscriptionState }}" x-data
        x-init="$nextTick(() => { if (!$el.open) $el.showModal() })"
        x-on:cw-show-subscription-notice.window="if (!$el.open) $el.showModal()">
        <div class="cw-subscription-dialog-content">
            <div class="cw-subscription-symbol" aria-hidden="true">!</div>
            <h2 id="cw-subscription-dialog-title">{{ $subscriptionState === 'expired' ? 'Your subscription has ended' : 'Your subscription is not active yet' }}</h2>
            <p>{{ $subscriptionState === 'expired' ? 'Please renew your subscription to restore access to your chatbot and dashboard features.' : 'Please contact your administrator to activate your subscription.' }}</p>
            <button type="button" class="cw-button cw-button-primary" autofocus x-on:click="$el.closest('dialog').close()">OK, understood</button>
        </div>
    </dialog>
@endif
