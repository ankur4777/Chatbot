@php($subscriptionCompany = auth('client')->user()?->company)
@if ($subscriptionCompany?->hasActiveSubscription() && $subscriptionCompany->subscription_expires_at)
    <script>
        (() => {
            const expiresAt = @json($subscriptionCompany->subscription_expires_at->getTimestamp() * 1000);
            const serverNow = @json(now()->getTimestamp() * 1000);
            const startedAt = performance.now();
            const target = @json(url('/client'));
            const check = () => {
                const remaining = expiresAt - serverNow - (performance.now() - startedAt);
                if (remaining <= 0) {
                    window.location.assign(target);
                    return;
                }
                window.setTimeout(check, Math.min(remaining + 1000, 60000));
            };
            check();
        })();
    </script>
@endif
