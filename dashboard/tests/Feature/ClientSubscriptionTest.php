<?php

namespace Tests\Feature;

use App\Filament\Client\SubscriptionGlobalSearchProvider;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Middleware\RequireClientSubscription;
use App\Models\Company;
use App\Models\User;
use App\Models\Website;
use App\Services\WebsiteFeatureService;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ClientSubscriptionTest extends TestCase
{
    public function test_expired_owner_can_sign_in_but_cannot_open_features(): void
    {
        $owner = $this->owner(now()->subSecond());

        $this->assertTrue($owner->canAccessPanel(Filament::getPanel('client')));
        $this->assertFalse($owner->canAccessPanel(Filament::getPanel('admin')));
        $this->assertSame('expired', $owner->company->subscriptionState());
        $this->assertSame(0, $owner->company->subscriptionDaysRemaining());
        $this->assertSame(200, $this->requestGate($owner, '/client')->getStatusCode());

        $response = $this->requestGate($owner, '/client/websites');
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/client', $response->getTargetUrl());
        $this->assertSame(302, $this->requestGate($owner, '/client/websites/download')->getStatusCode());
    }

    public function test_expired_livewire_and_direct_write_requests_are_denied(): void
    {
        $owner = $this->owner(now()->subDay());

        foreach ([['GET', ['X-Livewire' => 'true']], ['POST', []]] as [$method, $headers]) {
            try {
                $this->requestGate($owner, '/client/websites/1/edit', $method, $headers);
                $this->fail('Expired subscriptions cannot execute operational requests.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
    }

    public function test_renewal_restores_access_and_unlimited_subscription_has_no_countdown(): void
    {
        $owner = $this->owner(now()->subDay());
        $owner->company->subscription_expires_at = now()->addDays(3)->subMinute();

        $this->assertSame('active', $owner->company->subscriptionState());
        $this->assertSame(3, $owner->company->subscriptionDaysRemaining());
        $this->assertSame(200, $this->requestGate($owner, '/client/websites')->getStatusCode());

        $owner->company->subscription_expires_at = null;
        $this->assertTrue($owner->company->hasActiveSubscription());
        $this->assertNull($owner->company->subscriptionDaysRemaining());
    }

    public function test_scheduled_and_expired_subscriptions_disable_chatbot_and_live_chat(): void
    {
        $owner = $this->owner(now()->addDays(5));
        $website = new Website(['status' => true]);
        $website->setRelation('company', $owner->company);
        $website->setRelation('settings', null);
        $features = app(WebsiteFeatureService::class);

        $owner->company->subscription_starts_at = now()->addDay();
        $this->assertSame('scheduled', $owner->company->subscriptionState());
        $this->assertFalse($features->isChatbotEnabledForWebsite($website));
        $this->assertFalse($features->isLiveChatEnabledForWebsite($website));

        $owner->company->subscription_starts_at = now()->subDays(5);
        $owner->company->subscription_expires_at = now()->subSecond();
        $this->assertFalse($features->isChatbotEnabledForWebsite($website));
        $this->assertFalse($features->isLiveChatEnabledForWebsite($website));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Subscription is not active.');
        app(KnowledgeController::class)->chunks($website);
    }

    public function test_countdown_switches_to_hours_and_minutes_below_twenty_four_hours(): void
    {
        $this->freezeTime(function (): void {
            $company = $this->owner(now()->addDays(3))->company;
            $this->assertSame('3 days', $company->subscriptionRemainingLabel());

            $company->subscription_expires_at = now()->addHours(24);
            $this->assertSame('1 day', $company->subscriptionRemainingLabel());

            $company->subscription_expires_at = now()->addHours(24)->subSecond();
            $this->assertSame('23 hours 59 minutes', $company->subscriptionRemainingLabel());

            $company->subscription_expires_at = now()->addHours(2)->addMinutes(15);
            $this->assertSame('2 hours 15 minutes', $company->subscriptionRemainingLabel());

            $company->subscription_expires_at = now()->addMinutes(45);
            $this->assertSame('0 hours 45 minutes', $company->subscriptionRemainingLabel());

            $company->subscription_expires_at = now()->addSeconds(30);
            $this->assertSame('Less than 1 minute', $company->subscriptionRemainingLabel());

            $company->subscription_expires_at = now();
            $this->assertSame('0 days', $company->subscriptionRemainingLabel());
            $this->assertFalse($company->hasActiveSubscription());

            $company->subscription_expires_at = null;
            $this->assertSame('Unlimited', $company->subscriptionRemainingLabel());

            $company->subscription_starts_at = now()->addDay();
            $this->assertSame('Not active', $company->subscriptionRemainingLabel());
        });
    }

    private function owner($expiry): User
    {
        $company = new Company([
            'status' => true,
            'subscription_starts_at' => now()->subDays(2),
            'subscription_expires_at' => $expiry,
        ]);

        $owner = new User(['role' => 'owner', 'status' => true, 'company_id' => 1]);
        $owner->setRelation('company', $company);

        return $owner;
    }

    private function requestGate(User $owner, string $url, string $method = 'GET', array $headers = [])
    {
        $request = Request::create($url, $method);
        $request->setUserResolver(fn ($guard = null) => $owner);
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        return app(RequireClientSubscription::class)->handle($request, fn () => response('ok'));
    }
}
