<?php

namespace App\Filament\Client\Concerns;

use App\Models\Website;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

trait HasSelectedLiveChatWebsite
{
    #[Url(as: 'website')]
    public ?int $selectedLiveChatWebsite = null;

    public function liveChatWebsiteOptions(): Collection
    {
        return Website::query()
            ->where('company_id', $this->clientCompanyId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public function selectedLiveChatWebsiteId(): ?int
    {
        $sessionKey = $this->selectedLiveChatWebsiteSessionKey();
        $requestedWebsiteId = $this->selectedLiveChatWebsite
            ?? request()->query('website');

        if ($requestedWebsiteId !== null) {
            $requestedWebsiteId = (int) $requestedWebsiteId;

            abort_unless(
                $this->clientOwnsWebsite($requestedWebsiteId),
                404
            );

            session([$sessionKey => $requestedWebsiteId]);
            $this->selectedLiveChatWebsite = $requestedWebsiteId;

            return $requestedWebsiteId;
        }

        $sessionWebsiteId = session($sessionKey);

        if (
            $sessionWebsiteId
            && $this->clientOwnsWebsite((int) $sessionWebsiteId)
        ) {
            $this->selectedLiveChatWebsite = (int) $sessionWebsiteId;

            return (int) $sessionWebsiteId;
        }

        $firstWebsiteId = Website::query()
            ->where('company_id', $this->clientCompanyId())
            ->orderBy('name')
            ->value('id');

        if ($firstWebsiteId) {
            session([$sessionKey => (int) $firstWebsiteId]);
            $this->selectedLiveChatWebsite = (int) $firstWebsiteId;
        }

        return $firstWebsiteId ? (int) $firstWebsiteId : null;
    }

    public function updatedSelectedLiveChatWebsite($websiteId): void
    {
        $websiteId = (int) $websiteId;

        abort_unless(
            $this->clientOwnsWebsite($websiteId),
            404
        );

        session([$this->selectedLiveChatWebsiteSessionKey() => $websiteId]);
        $this->selectedLiveChatWebsite = $websiteId;

        if (method_exists($this, 'resetSelectedLiveChatAgentIfInvalid')) {
            $this->resetSelectedLiveChatAgentIfInvalid();
        }

        if (method_exists($this, 'resetTable')) {
            $this->resetTable();
        }
    }

    protected function clientOwnsWebsite(int $websiteId): bool
    {
        return Website::query()
            ->whereKey($websiteId)
            ->where('company_id', $this->clientCompanyId())
            ->exists();
    }

    protected function selectedLiveChatWebsiteSessionKey(): string
    {
        return 'client.live_chat.selected_website.' . $this->clientCompanyId();
    }
}
