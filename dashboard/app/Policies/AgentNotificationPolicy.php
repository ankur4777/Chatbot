<?php

namespace App\Policies;

use App\Models\AgentNotification;
use App\Models\User;

class AgentNotificationPolicy
{
    public function view(User $user, AgentNotification $notification): bool
    {
        return $user->role === 'agent'
            && $notification->agent_id === $user->id
            && $notification->company_id === $user->company_id;
    }

    public function update(User $user, AgentNotification $notification): bool
    {
        return $this->view($user, $notification);
    }

    public function delete(User $user, AgentNotification $notification): bool
    {
        return $this->view($user, $notification);
    }
}
