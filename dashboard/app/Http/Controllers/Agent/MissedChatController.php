<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ChatbotLead;
use Carbon\Carbon;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MissedChatController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = $this->assignedMissedChats($request);

        return view('agent.missed-chats.index', [
            'leads' => (clone $baseQuery)
                ->with(['website', 'visitor', 'assignedAgent'])
                ->latest('updated_at')
                ->paginate(20),
            'missedStats' => [
                'total' => (clone $baseQuery)->count(),
                'follow_up_required' => (clone $baseQuery)
                    ->whereIn('followup_status', ['pending', 'assigned', 'follow_up_required'])
                    ->count(),
                'contacted' => (clone $baseQuery)
                    ->where('followup_status', 'contacted')
                    ->count(),
                'resolved' => (clone $baseQuery)
                    ->where('followup_status', 'resolved')
                    ->count(),
            ],
        ]);
    }

    public function show(Request $request, ChatbotLead $lead): View
    {
        return view('agent.missed-chats.show', [
            'lead' => $this->missedChatForAgent($request, $lead),
            'statuses' => $this->agentFollowupStatusLabels(),
        ]);
    }

    public function update(
        Request $request,
        ChatbotLead $lead
    ): RedirectResponse {
        $lead = $this->missedChatForAgent($request, $lead);

        $data = $request->validate([
            'followup_status' => [
                'required',
                'string',
                Rule::in(array_keys($this->agentFollowupStatusLabels())),
            ],
            'agent_note' => ['nullable', 'string', 'max:5000'],
            'last_contacted_at' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
                'before_or_equal:2038-01-19T03:14',
            ],
            'next_followup_at' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
                'before_or_equal:2038-01-19T03:14',
            ],
            'remind_before_followup' => ['nullable', 'boolean'],
        ]);

        $status = $data['followup_status'];
        $previousStatus = $lead->followup_status ?? 'pending';
        $lastContactedAt = $this->browserDateTimeToAppTimezone(
            $data['last_contacted_at'] ?? null
        );
        $resolvedAt = $lead->resolved_at;

        if ($status === 'contacted' && $previousStatus !== 'contacted') {
            $lastContactedAt = now();
        }

        if ($status === 'resolved' && $previousStatus !== 'resolved') {
            $resolvedAt = now();
        }

        if ($status !== 'resolved') {
            $resolvedAt = null;
        }

        $nextFollowupAt = $this->browserDateTimeToAppTimezone(
            $data['next_followup_at'] ?? null
        );
        $reminderEnabled = (bool) ($data['remind_before_followup'] ?? false)
            && $nextFollowupAt
            && $nextFollowupAt->isFuture()
            && ! in_array($status, ['resolved', 'unable_to_reach'], true);
        $previousReminderAt = $lead->followup_reminder_at?->toISOString();
        $nextReminderAt = $reminderEnabled
            ? $nextFollowupAt->copy()
            : null;
        $reminderChanged = $previousReminderAt !== $nextReminderAt?->toISOString();

        $lead->forceFill([
            'followup_status' => $status,
            'agent_note' => trim((string) ($data['agent_note'] ?? '')) ?: null,
            'last_contacted_at' => $lastContactedAt,
            'next_followup_at' => $nextFollowupAt,
            'followup_reminder_enabled' => $reminderEnabled,
            'followup_reminder_at' => $nextReminderAt,
            'followup_reminder_sent_at' => $reminderEnabled && ! $reminderChanged
                ? $lead->followup_reminder_sent_at
                : null,
            'resolved_at' => $resolvedAt,
        ])->save();

        return redirect()
            ->route('agent.missed-chats.show', $lead)
            ->with('status', 'Follow-up updated.');
    }

    protected function assignedMissedChats(Request $request)
    {
        $agent = $request->user();

        return ChatbotLead::query()
            ->where('source', 'live_chat_offline_request')
            ->where('assigned_agent_id', $agent->id)
            ->whereHas(
                'website',
                fn ($query) => $query->where('company_id', $agent->company_id)
            );
    }

    protected function missedChatForAgent(
        Request $request,
        ChatbotLead $lead
    ): ChatbotLead {
        return $this->assignedMissedChats($request)
            ->whereKey($lead->id)
            ->with(['website', 'visitor', 'assignedAgent', 'assignedBy'])
            ->firstOrFail();
    }

    protected function browserDateTimeToAppTimezone(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        $timezone = FilamentTimezone::get()
            ?? config('app.display_timezone', 'Asia/Kolkata');

        return Carbon::createFromFormat('Y-m-d\TH:i', $value, $timezone)
            ->timezone(config('app.timezone', 'UTC'));
    }

    protected function agentFollowupStatusLabels(): array
    {
        return collect(ChatbotLead::followupStatusLabels())
            ->except(['pending', 'assigned'])
            ->all();
    }
}
