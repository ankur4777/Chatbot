<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteGalleryImage;
use App\Services\WebsiteFeatureService;

class WebsiteGalleryImagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status && ($user->role === 'super_admin'
            || app(WebsiteFeatureService::class)->isImageGalleryEnabledForAuthenticatedUserCompany($user));
    }

    public function create(User $user): bool
    {
        return app(WebsiteFeatureService::class)->isImageGalleryEnabledForAuthenticatedUserCompany($user);
    }

    public function view(User $user, WebsiteGalleryImage $image): bool
    {
        return $this->allowed($user, $image);
    }

    public function update(User $user, WebsiteGalleryImage $image): bool
    {
        return $this->allowed($user, $image);
    }

    public function delete(User $user, WebsiteGalleryImage $image): bool
    {
        return $this->allowed($user, $image);
    }

    private function allowed(User $user, WebsiteGalleryImage $image): bool
    {
        if ($user->status && $user->role === 'super_admin') {
            return true;
        }

        return $image->company_id === $user->company_id
            && app(WebsiteFeatureService::class)->galleryWebsitesForUser($user)
                ->whereKey($image->website_id)->exists();
    }
}
