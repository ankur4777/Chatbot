<?php

namespace App\Providers;

use App\Models\ChatConversation;
use App\Models\ChatbotLead;
use App\Models\AgentNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('widget-init', function (Request $request): Limit {
            return Limit::perMinute(60)->by(
                $this->widgetRateLimitKey($request)
            );
        });

        RateLimiter::for('widget-message', function (Request $request): Limit {
            return Limit::perMinute(30)->by(
                $this->widgetRateLimitKey($request)
            );
        });

        RateLimiter::for('widget-live-chat', function (Request $request): Limit {
            return Limit::perMinute(20)->by(
                $this->widgetRateLimitKey($request)
            );
        });

        RateLimiter::for('widget-realtime', function (Request $request): Limit {
            return Limit::perMinute(120)->by(
                $this->widgetRateLimitKey($request)
            );
        });

        View::composer('agent.*', function ($view): void {
            $agent = Auth::user();

            if (
                ! $agent
                || $agent->role !== 'agent'
                || ! $agent->company_id
            ) {
                return;
            }

            AgentNotification::query()
                ->where('agent_id', $agent->id)
                ->where('created_at', '<', today())
                ->delete();

            $companyConversations = ChatConversation::query()
                ->whereHas(
                    'website',
                    fn ($query) => $query
                        ->where('company_id', $agent->company_id)
                        ->whereHas(
                            'liveChatAgents',
                            fn ($query) => $query->whereKey($agent->id)
                        )
                );

            $view->with([
                'agentNavWaitingCount' => (clone $companyConversations)
                    ->where('status', 'waiting_agent')
                    ->count(),
                'agentNavActiveCount' => (clone $companyConversations)
                    ->where('status', 'live_active')
                    ->where('assigned_agent_id', $agent->id)
                    ->count(),
                'agentNavMissedCount' => ChatbotLead::query()
                    ->where('source', 'live_chat_offline_request')
                    ->where('assigned_agent_id', $agent->id)
                    ->whereIn('followup_status', [
                        'pending',
                        'assigned',
                        'follow_up_required',
                    ])
                    ->whereHas(
                        'website',
                        fn ($query) => $query->where(
                            'company_id',
                            $agent->company_id
                        )
                    )
                    ->count(),
                'agentNavNotificationCount' => AgentNotification::query()
                    ->where('agent_id', $agent->id)
                    ->unread()
                    ->count(),
                'agentLatestNotifications' => AgentNotification::query()
                    ->where('agent_id', $agent->id)
                    ->latest()
                    ->limit(5)
                    ->get(),
                'agentLiveNotificationConversations' => (clone $companyConversations)
                    ->with('visitor')
                    ->where('status', 'live_active')
                    ->where('assigned_agent_id', $agent->id)
                    ->latest('updated_at')
                    ->limit(50)
                    ->get()
                    ->map(fn (ChatConversation $conversation): array => [
                        'id' => $conversation->id,
                        'label' => $conversation->visitor?->displayName() ?? 'Visitor',
                        'url' => route('agent.chats.show', $conversation),
                    ])
                    ->values(),
            ]);
        });
    }

    protected function widgetRateLimitKey(Request $request): string
    {
        return implode('|', array_filter([
            $request->ip(),
            $request->input('widget_key', $request->query('widget_key')),
            $request->input('visitor_uuid', $request->query('visitor_uuid')),
            $request->input('session_id', $request->query('session_id')),
        ]));
    }
}
