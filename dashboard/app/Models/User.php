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
use Filament\Models\Contracts\FilamentUser;

#[Fillable([
    'company_id',
    'name',
    'email',
    'password',
    'role',
    'status',
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
public function canAccessPanel(Panel $panel): bool
{
    // Inactive user cannot access any dashboard
    if (! $this->status) {
        return false;
    }

    // Owner's company must also be active
    if ($this->role === 'owner') {
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
