<?php

namespace App\Filament\Agent\Pages;

use App\Filament\Agent\Concerns\HasAgentAccess;
use App\Models\ChatConversation;
use Illuminate\Support\Collection;
use Filament\Panel;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class AgentDashboard extends Page
{
    use HasAgentAccess;

    protected static ?string $title = 'Agent Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string $routePath = '/';

    protected static string|\BackedEnum|null $navigationIcon =
        Heroicon::OutlinedHome;

    protected string $view = 'filament.agent.pages.agent-dashboard';

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    public function getStats(): array
    {
        $companyId = $this->agentCompanyId();
        $agentId = auth()->id();

        return [
            'waiting' => ChatConversation::query()
                ->whereHas(
                    'website',
                    fn ($query) => $query->where('company_id', $companyId)
                )
                ->where('status', 'waiting_agent')
                ->count(),

            'active' => ChatConversation::query()
                ->whereHas(
                    'website',
                    fn ($query) => $query->where('company_id', $companyId)
                )
                ->where('status', 'live_active')
                ->where('assigned_agent_id', $agentId)
                ->count(),

            'closed_today' => ChatConversation::query()
                ->whereHas(
                    'website',
                    fn ($query) => $query->where('company_id', $companyId)
                )
                ->whereIn('status', ['closed', 'resolved'])
                ->whereDate('updated_at', today())
                ->count(),
        ];
    }

    public function getWaitingConversations(): Collection
    {
        return ChatConversation::query()
            ->with(['visitor', 'website'])
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $this->agentCompanyId()
                )
            )
            ->where('status', 'waiting_agent')
            ->latest('handoff_requested_at')
            ->limit(6)
            ->get();
    }

    public function getMyConversations(): Collection
    {
        return ChatConversation::query()
            ->with(['visitor', 'website'])
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $this->agentCompanyId()
                )
            )
            ->where('status', 'live_active')
            ->where('assigned_agent_id', auth()->id())
            ->latest('updated_at')
            ->limit(6)
            ->get();
    }
}
