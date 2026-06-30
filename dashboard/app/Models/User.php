<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'name',
    'email',
    'password',
    'role',
    'status',
    'is_online',
    'last_seen_at',
])]

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
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
}
