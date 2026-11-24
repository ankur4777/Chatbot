<?php

namespace App\Filament\Client;

use App\Filament\Client\Pages\LiveInbox;
use App\Filament\Client\Pages\ActiveChats;
use App\Filament\Client\Resources\ChatbotLeads\ChatbotLeadResource;
use App\Filament\Client\Resources\ChatConversations\ChatConversationResource;
use App\Filament\Client\Resources\Visitors\VisitorResource;
use App\Filament\Client\Resources\Websites\WebsiteResource;
use App\Models\ChatbotLead;
use App\Models\ChatConversation;
use App\Models\Website;
use Filament\Pages\Dashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ClientDashboard extends Dashboard
{
    protected static ?string $title = 'Overview';
    protected static ?string $navigationLabel = 'Overview';
    protected string $view = 'filament.client.pages.dashboard';

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    protected function getViewData(): array
    {
        $user = auth('client')->user();
        abort_unless($user?->role === 'owner' && $user->company_id, 403);

        $company = $user->company;
        abort_unless($company && $user->status && $company->status, 403);

        $subscription = [
            'subscriptionState' => $company->subscriptionState(),
            'subscriptionStart' => $company->subscription_starts_at,
            'subscriptionEnd' => $company->subscription_expires_at,
            'subscriptionDays' => $company->subscriptionDaysRemaining(),
            'subscriptionRemainingLabel' => $company->subscriptionRemainingLabel(),
        ];

        if (! $company->hasActiveSubscription()) {
            // Do not load operational records for an expired or scheduled subscription.
            return $subscription + [
                'clientName' => str($user->name)->before(' ')->toString(),
                'companyName' => $company->name,
            ];
        }

        $companyId = (int) $user->company_id;
        $conversations = ChatConversation::query()->whereHas(
            'website', fn (Builder $query) => $query->where('company_id', $companyId)
        );
        $leads = ChatbotLead::query()->whereHas(
            'website', fn (Builder $query) => $query->where('company_id', $companyId)
        )->where(fn (Builder $query) => $query->whereNull('source')
            ->orWhere('source', '!=', 'live_chat_offline_request'));

        $hasLiveChat = $user->hasLiveChatAccess();

        return $subscription + [
            'clientName' => str($user->name)->before(' ')->toString(),
            'companyName' => $user->company?->name ?? 'Your company',
            'websiteCount' => Website::query()->where('company_id', $companyId)->count(),
            'conversationCount' => (clone $conversations)->count(),
            'recentConversationCount' => (clone $conversations)->where('created_at', '>=', now()->subDays(7))->count(),
            'leadCount' => (clone $leads)->count(),
            'recentLeadCount' => (clone $leads)->where('created_at', '>=', now()->subDays(7))->count(),
            'waitingCount' => $hasLiveChat ? (clone $conversations)->where('status', 'waiting_agent')->count() : null,
            'activeChatCount' => $hasLiveChat ? (clone $conversations)->where('status', 'live_active')->count() : null,
            'hasLiveChat' => $hasLiveChat,
            'recentConversations' => (clone $conversations)
                ->with(['website:id,name', 'visitor:id,name,visitor_uuid'])
                ->latest('updated_at')->limit(6)->get(),
            'recentLeads' => (clone $leads)->with('website:id,name')
                ->latest('created_at')->limit(5)->get(),
            'links' => [
                'websites' => WebsiteResource::getUrl('index', panel: 'client'),
                'conversations' => ChatConversationResource::getUrl('index', panel: 'client'),
                'leads' => ChatbotLeadResource::getUrl('index', panel: 'client'),
                'visitors' => VisitorResource::getUrl('index', panel: 'client'),
                'inbox' => $hasLiveChat ? LiveInbox::getUrl(panel: 'client') : null,
                'activeChats' => $hasLiveChat ? ActiveChats::getUrl(panel: 'client') : null,
                'waitingChats' => $hasLiveChat ? \App\Filament\Client\Pages\WaitingChats::getUrl(panel: 'client') : null,
            ],
        ];
    }
}
