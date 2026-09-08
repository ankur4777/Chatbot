<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\Website;

class WebsiteFeatureService
{
    public function isChatbotEnabledForWebsite(Website $website): bool
    {
        return $website->settings?->enable_chatbot !== false;
    }

    public function isLiveChatEnabledForWebsite(Website $website): bool
    {
        return $website->settings?->enable_live_chat === true;
    }

    public function isAiResponsesEnabledForWebsite(Website $website): bool
    {
        return $website->settings?->enable_ai_responses !== false;
    }

    public function isAiResponsesEnabledForCompany(Company|int $company): bool
    {
        $companyId = $company instanceof Company
            ? $company->getKey()
            : $company;

        $hasWebsites = Website::query()
            ->where('company_id', $companyId)
            ->exists();

        if (! $hasWebsites) {
            return false;
        }

        return ! Website::query()
            ->where('company_id', $companyId)
            ->whereHas('settings', function ($settingsQuery): void {
                $settingsQuery->where('enable_ai_responses', false);
            })
            ->exists();
    }

    public function isAiResponsesEnabledForAuthenticatedUserCompany(
        ?User $user = null
    ): bool {
        $user ??= auth()->user();

        if (! $user || ! $user->company_id) {
            return false;
        }

        return $this->isAiResponsesEnabledForCompany(
            $user->company_id
        );
    }

    public function isLiveChatEnabledForCompany(Company|int $company): bool
    {
        $companyId = $company instanceof Company
            ? $company->getKey()
            : $company;

        return Website::query()
            ->where('company_id', $companyId)
            ->whereHas('settings', function ($query): void {
                $query->where('enable_live_chat', true);
            })
            ->exists();
    }

    public function isLiveChatEnabledForAuthenticatedUserCompany(
        ?User $user = null
    ): bool {
        $user ??= auth()->user();

        if (! $user || ! $user->company_id) {
            return false;
        }

        return $this->isLiveChatEnabledForCompany(
            $user->company_id
        );
    }

    public function userHasLiveChatAccess(?User $user): bool
    {
        if (! $user || ! $user->status) {
            return false;
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        if (! in_array($user->role, ['owner', 'agent'], true)) {
            return false;
        }

        if (! $user->company_id || ! $user->company?->status) {
            return false;
        }

        return $this->isLiveChatEnabledForAuthenticatedUserCompany($user);
    }
}
