<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'logo',
        'status',
    ];

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
}