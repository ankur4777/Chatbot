<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBase extends Model
{
    protected $fillable = [
        'knowledge_category_id',
        'title',
        'content',
        'source_type',
        'source_file',
        'source_url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledge_category_id');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeChunk::class);
    }
}