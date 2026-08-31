<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotFlowStep extends Model
{
    protected $fillable = [
        'chatbot_flow_id',
        'step_order',
        'step_key',
        'question',
        'input_type',
        'placeholder',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ChatbotFlowStep $step) {
            $step->input_type = 'buttons';
        });
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ChatbotFlow::class, 'chatbot_flow_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ChatbotFlowOption::class)
            ->orderBy('sort_order');
    }
    public function answers(): HasMany
{
    return $this->hasMany(ChatbotFlowAnswer::class);
}

}
