<?php

namespace App\Filament\Client\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

trait HasSelectedLiveChatAgent
{
    #[Url(as: 'agent')]
    public ?int $selectedLiveChatAgent = null;

    public function liveChatAgentOptions(): Collection
    {
        $websiteId = $this->selectedLiveChatWebsiteId();

        if (! $websiteId) {
            return collect();
        }

        return User::query()
            ->where('company_id', $this->clientCompanyId())
            ->where('role', 'agent')
            ->whereHas(
                'assignedWebsites',
                fn ($query) => $query->whereKey($websiteId)
            )
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public function selectedLiveChatAgentId(): ?int
    {
        if (! $this->selectedLiveChatAgent) {
            return null;
        }

        $agentId = (int) $this->selectedLiveChatAgent;

        if (! $this->clientCanFilterByAgent($agentId)) {
            $this->selectedLiveChatAgent = null;

            return null;
        }

        return $agentId;
    }

    public function updatedSelectedLiveChatAgent($agentId): void
    {
        $this->selectedLiveChatAgent = $agentId ? (int) $agentId : null;

        if (
            $this->selectedLiveChatAgent
            && ! $this->clientCanFilterByAgent($this->selectedLiveChatAgent)
        ) {
            $this->selectedLiveChatAgent = null;
        }

        if (method_exists($this, 'resetTable')) {
            $this->resetTable();
        }
    }

    public function resetSelectedLiveChatAgentIfInvalid(): void
    {
        if (
            $this->selectedLiveChatAgent
            && ! $this->clientCanFilterByAgent((int) $this->selectedLiveChatAgent)
        ) {
            $this->selectedLiveChatAgent = null;
        }
    }

    protected function clientCanFilterByAgent(int $agentId): bool
    {
        $websiteId = $this->selectedLiveChatWebsiteId();

        if (! $websiteId) {
            return false;
        }

        return User::query()
            ->whereKey($agentId)
            ->where('company_id', $this->clientCompanyId())
            ->where('role', 'agent')
            ->whereHas(
                'assignedWebsites',
                fn ($query) => $query->whereKey($websiteId)
            )
            ->exists();
    }
}
