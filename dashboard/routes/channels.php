<?php

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('live-chat.{conversationId}', function (User $user, int $conversationId): bool {
    if (
        $user->role !== 'agent'
        || ! $user->status
        || ! $user->company_id
        || ! $user->company?->status
    ) {
        return false;
    }

    $conversation = ChatConversation::query()
        ->with('website')
        ->whereKey($conversationId)
        ->first();

    if (! $conversation) {
        return false;
    }

    if ($conversation->website?->company_id !== $user->company_id) {
        return false;
    }

    if ($conversation->status === 'waiting_agent') {
        return true;
    }

    if (in_array($conversation->status, ['live_active', 'closed'], true)) {
        return $conversation->assigned_agent_id === $user->id;
    }

    return false;
});

Broadcast::channel('company-live-chat.{companyId}', function (User $user, int $companyId): bool {
    return $user->role === 'agent'
        && $user->status
        && $user->company_id === $companyId
        && (bool) $user->company?->status;
});
