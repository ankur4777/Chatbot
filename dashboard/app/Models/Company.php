<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
       'name',
    'owner_name',
    'email',
    'phone',
    'gst_number',
    'address',
    'slug',
    'logo',
    'status',
    ];

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }
    public function users()
{
    return $this->hasMany(User::class);
}
}