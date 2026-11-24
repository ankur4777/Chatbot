<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
       'name',
    'legal_name',
    'owner_name',
    'email',
    'phone',
    'gst_number',
    'address',
    'county_name',
    'country_code',
    'slug',
    'logo',
    'status',
    'subscription_starts_at',
    'subscription_expires_at',
    'conversation_retention_months',
    ];

    protected $casts = [
        'status' => 'boolean',
        'subscription_starts_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'conversation_retention_months' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Company $company): void {
            if (filled($company->legal_name)) {
                $company->name = $company->legal_name;
            } elseif (filled($company->name)) {
                $company->legal_name = $company->name;
            }
        });
    }

    public function hasActiveSubscription(): bool
    {
        return $this->status
            && (! $this->subscription_starts_at || ! $this->subscription_starts_at->isFuture())
            && (! $this->subscription_expires_at || $this->subscription_expires_at->isFuture());
    }

    public function subscriptionState(): string
    {
        if (! $this->status) {
            return 'inactive';
        }

        if ($this->subscription_expires_at && ! $this->subscription_expires_at->isFuture()) {
            return 'expired';
        }

        if ($this->subscription_starts_at?->isFuture()) {
            return 'scheduled';
        }

        return 'active';
    }

    public function subscriptionDaysRemaining(): ?int
    {
        if (! $this->subscription_expires_at) {
            return null;
        }

        return (int) ceil(max(0, $this->subscription_expires_at->getTimestamp() - now()->getTimestamp()) / 86400);
    }

    public function conversationRetentionMonths(): int
    {
        return max(1, min(6, (int) ($this->conversation_retention_months ?: 1)));
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
    public function users()
{
    return $this->hasMany(User::class);
}
}
