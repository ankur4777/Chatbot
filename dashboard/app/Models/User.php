<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Filament\Models\Contracts\FilamentUser;
use App\Services\WebsiteFeatureService;

#[Fillable([
    'company_id',
    'name',
    'email',
    'phone',
    'password',
    'role',
    'status',
    'is_online',
    'availability_status',
    'last_seen_at',
])]

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',

        'status' => 'boolean',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
    ];
}
public function company()
{
    return $this->belongsTo(Company::class);
}
public function assignedConversations()
{
    return $this->hasMany(ChatConversation::class, 'assigned_agent_id');
}

public function assignedWebsites(): BelongsToMany
{
    return $this->belongsToMany(Website::class, 'website_agent', 'agent_id', 'website_id')
        ->withTimestamps();
}

public function liveChatSessions()
{
    return $this->hasMany(LiveChatSession::class, 'agent_id');
}

public function hasLiveChatAccess(): bool
{
    return app(WebsiteFeatureService::class)
        ->userHasLiveChatAccess($this);
}

public function isAvailableForLiveChat(): bool
{
    return $this->role === 'agent'
        && $this->status
        && $this->company_id
        && $this->availability_status === 'online';
}

public function canAccessPanel(Panel $panel): bool
{
    // Inactive user cannot access any dashboard
    if (! $this->status) {
        return false;
    }

    // Company users must belong to an active company.
    if (in_array($this->role, ['owner', 'agent'], true)) {
        if (! $this->company || ! $this->company->status) {
            return false;
        }
    }

    if ($panel->getId() === 'admin') {
        return $this->role === 'super_admin';
    }

    if ($panel->getId() === 'client') {
        return $this->role === 'owner';
    }

    return false;
}
}
