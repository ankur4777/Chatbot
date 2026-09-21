<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ChatbotLead;
use App\Models\LiveChatClosure;
use App\Models\LiveChatRating;
use App\Models\LiveChatSession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Filament\Support\Facades\FilamentTimezone;

class ProfileController extends Controller
{
    public function show(Request $request): View
{
        $agent = $request->user()->load(['company', 'assignedWebsites']);
        $timezone = $this->browserTimezone();
        $selectedMonth = $this->selectedMonth($request);
        $activityCalendar = $this->activityCalendar($agent, $selectedMonth);

        return view('agent.profile', [
            'agent' => $agent,
        
        'activityCalendar' => $activityCalendar,
        'closedSessionsCount' => $this->closedSessionsCount($agent),
        'closedLast30DaysCount' => $this->closedLast30DaysCount($agent),
        'ratingStats' => $this->ratingStats($agent->id),
        'ratingLast30DaysStats' => $this->ratingLast30DaysStats($agent),

        'lastSeenDisplay' => $agent->last_seen_at
            ? Carbon::parse($agent->last_seen_at)
                ->setTimezone($timezone)
                ->format('d M Y, h:i A')
            : null,
    ]);
}

    protected function selectedMonth(Request $request): Carbon
    {
        $timezone = $this->browserTimezone();
        $month = $request->query('month');

        if (! is_string($month) || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return now()->timezone($timezone)->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $month, $timezone)->startOfMonth();
        } catch (\Throwable) {
            return now()->timezone($timezone)->startOfMonth();
        }
    }

    protected function ratingStats(int $agentId): array
    {
        $query = LiveChatRating::query()
            ->where('agent_id', $agentId);

        return [
            'average' => (clone $query)->avg('rating'),
            'count' => (clone $query)->count(),
        ];
    }

    protected function closedSessionsCount($agent): int
    {
        return LiveChatClosure::query()
            ->where('agent_id', $agent->id)
            ->where('company_id', $agent->company_id)
            ->count();
    }

    protected function closedLast30DaysCount($agent): int
    {
        $cutoff = $this->databaseNow()
            ->copy()
            ->subDays(30)
            ->timezone(config('app.timezone', 'UTC'));

        return LiveChatClosure::query()
            ->where('agent_id', $agent->id)
            ->where('company_id', $agent->company_id)
            ->where('ended_at', '>=', $cutoff)
            ->count();
    }

    protected function ratingLast30DaysStats($agent): array
    {
        $cutoff = $this->databaseNow()
            ->copy()
            ->subDays(30)
            ->timezone(config('app.timezone', 'UTC'));

        $query = LiveChatRating::query()
            ->where('agent_id', $agent->id)
            ->where('company_id', $agent->company_id)
            ->where('submitted_at', '>=', $cutoff);

        return [
            'average' => (clone $query)->avg('rating'),
            'count' => (clone $query)->count(),
        ];
    }

    protected function activityCalendar($agent, ?Carbon $selectedMonth = null): array
    {
        $now = $this->databaseNow();
        $start = ($selectedMonth ?: $now)->copy()->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $queryStart = $start->copy()->timezone(config('app.timezone', 'UTC'))->toDateTimeString();
        $queryEnd = $end->copy()->timezone(config('app.timezone', 'UTC'))->toDateTimeString();
        $todayKey = $now->toDateString();
        $selectedKey = $start->isSameMonth($now) ? $todayKey : $start->toDateString();
        $days = [];
        $hasOpenLogin = false;
        $hasOpenBreak = false;

        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->toDateString();
            $days[$key] = [
                'date' => $key,
                'day' => $day->day,
                'label' => $day->format('d M Y'),
                'login_seconds' => 0,
                'break_seconds' => 0,
                'first_login_at' => null,
                'latest_login_at' => null,
                'first_logout_at' => null,
                'login_time' => 'N/A',
                'logout_time' => 'N/A',
                'online_time' => '0m',
                'break_time' => '0m',
                'closed_chats_count' => 0,
                'missed_chats_count' => 0,
                'online_running' => false,
                'break_running' => false,
                'is_today' => $key === $todayKey,
                'is_selected' => $key === $selectedKey,
            ];
        }

        LiveChatSession::query()
            ->select('ended_at')
            ->where('agent_id', $agent->id)
            ->whereBetween('ended_at', [$queryStart, $queryEnd])
            ->whereHas(
                'conversation.website',
                fn ($query) => $query->where('company_id', $agent->company_id)
            )
            ->get()
            ->each(function ($session) use (&$days): void {
                $key = $this->databaseTime($session->ended_at)->toDateString();

                if (isset($days[$key])) {
                    $days[$key]['closed_chats_count']++;
                }
            });

        ChatbotLead::query()
            ->select('created_at')
            ->where('assigned_agent_id', $agent->id)
            ->where('source', 'live_chat_offline_request')
            ->whereBetween('created_at', [$queryStart, $queryEnd])
            ->whereHas(
                'website',
                fn ($query) => $query->where('company_id', $agent->company_id)
            )
            ->get()
            ->each(function ($lead) use (&$days): void {
                $key = $this->databaseTime($lead->created_at)->toDateString();

                if (isset($days[$key])) {
                    $days[$key]['missed_chats_count']++;
                }
            });

        $activityLogs = DB::table('agent_activity_logs')
            ->where('agent_id', $agent->id)
            ->where('started_at', '<=', $queryEnd)
            ->where(function ($query) use ($queryStart): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $queryStart);
            })
            ->orderBy('started_at')
            ->orderBy('id')
            ->get()
            ->values();

        $activityLogs->each(function ($log, int $index) use ($activityLogs, &$days, &$hasOpenLogin, &$hasOpenBreak, $agent, $start, $end, $now, $todayKey): void {
                if (in_array($log->type, ['logout', 'session_login'], true)) {
                    $activityAt = $this->databaseTime($log->started_at);
                    $key = $activityAt->toDateString();

                    if (
                        isset($days[$key])
                        && $log->type === 'logout'
                        && (
                            ! $days[$key]['first_logout_at']
                            || $activityAt->lessThan($days[$key]['first_logout_at'])
                        )
                    ) {
                        $days[$key]['first_logout_at'] = $activityAt;
                    }

                    if (
                        isset($days[$key])
                        && $log->type === 'session_login'
                        && (
                            ! $days[$key]['first_login_at']
                            || $activityAt->lessThan($days[$key]['first_login_at'])
                        )
                    ) {
                        $days[$key]['first_login_at'] = $activityAt;
                    }

                    if (
                        isset($days[$key])
                        && $log->type === 'session_login'
                        && (
                            ! $days[$key]['latest_login_at']
                            || $activityAt->greaterThan($days[$key]['latest_login_at'])
                        )
                    ) {
                        $days[$key]['latest_login_at'] = $activityAt;
                    }

                    return;
                }

                if (! in_array($log->type, ['login', 'break'], true)) {
                    return;
                }

                $periodStart = $this->databaseTime($log->started_at)->max($start);

                if (blank($log->ended_at)) {
                    $nextActivity = $activityLogs
                        ->slice($index + 1)
                        ->first(fn ($nextLog) => in_array($nextLog->type, ['login', 'break', 'logout', 'session_login'], true));

                    if ($nextActivity) {
                        $periodEnd = $this->databaseTime($nextActivity->started_at);
                    } else {
                        $isCurrentStatus = ($log->type === 'login' && $agent->availability_status === 'online')
                            || ($log->type === 'break' && $agent->availability_status === 'away');

                        $periodEnd = $isCurrentStatus
                            ? $now
                            : ($agent->last_seen_at
                                ? $this->databaseTime($agent->last_seen_at)
                                : $this->databaseTime($log->started_at));

                        if ($isCurrentStatus) {
                            if ($log->type === 'login') {
                                $hasOpenLogin = true;
                            } else {
                                $hasOpenBreak = true;
                            }
                        }
                    }
                } else {
                    $periodEnd = $this->databaseTime($log->ended_at);
                }

                $periodEnd = $periodEnd->min($end);

                if ($periodEnd->lessThanOrEqualTo($periodStart)) {
                    return;
                }

                $cursor = $periodStart->copy()->startOfDay();
                $lastDay = $periodEnd->copy()->startOfDay();

                while ($cursor->lessThanOrEqualTo($lastDay)) {
                    $key = $cursor->toDateString();

                    if (! isset($days[$key])) {
                        $cursor->addDay();
                        continue;
                    }

                    $dayStart = $cursor->copy()->startOfDay();
                    $dayEnd = $cursor->copy()->endOfDay();
                    $segmentStart = $periodStart->greaterThan($dayStart) ? $periodStart : $dayStart;
                    $segmentEnd = $periodEnd->lessThan($dayEnd) ? $periodEnd : $dayEnd;
                    $seconds = max(0, $segmentStart->diffInSeconds($segmentEnd));

                    if ($log->type === 'break') {
                        $days[$key]['break_seconds'] += $seconds;
                    } elseif ($log->type === 'login') {
                        $days[$key]['login_seconds'] += $seconds;

                    }

                    if (blank($log->ended_at) && ! isset($nextActivity) && $key === $todayKey) {
                        if (
                            $log->type === 'break'
                            && $agent->availability_status === 'away'
                        ) {
                            $days[$key]['break_running'] = true;
                        } elseif ($log->type !== 'break') {
                            $days[$key]['online_running'] = true;
                        }
                    }

                    $cursor->addDay();
                }
            });

        if (
            ! $hasOpenLogin
            && $agent->availability_status === 'online'
            && isset($days[$todayKey])
        ) {
            $fallbackStart = ($agent->last_seen_at ? $this->databaseTime($agent->last_seen_at) : $now)
                ->max($now->copy()->startOfDay());
            $days[$todayKey]['login_seconds'] += max(0, $fallbackStart->diffInSeconds($now));
            $days[$todayKey]['online_running'] = true;
        }

        if (
            ! $hasOpenBreak
            && $agent->availability_status === 'away'
            && isset($days[$todayKey])
        ) {
            $fallbackStart = ($agent->last_seen_at ? $this->databaseTime($agent->last_seen_at) : $now)
                ->max($now->copy()->startOfDay());
            $days[$todayKey]['break_seconds'] += max(0, $fallbackStart->diffInSeconds($now));
            $days[$todayKey]['break_running'] = true;
        }

        foreach ($days as &$day) {
            $day['online_running'] = $day['online_running']
                && ! $day['break_running']
                && $agent->availability_status === 'online';
            $day['online_time'] = $this->formatDuration($day['login_seconds']);
            $day['break_time'] = $this->formatDuration($day['break_seconds']);
            $day['login_time'] = $day['first_login_at']
                ? $this->formatTime($day['first_login_at'])
                : 'N/A';
            $day['logout_time'] = $day['first_logout_at']
                ? $this->formatTime($day['first_logout_at'])
                : ($day['online_running'] ? 'Active now' : 'N/A');
        }

        return [
            'month' => $start->format('F Y'),
            'selectedMonth' => $start->format('Y-m'),
            'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'currentMonth' => $now->copy()->startOfMonth()->format('Y-m'),
            'monthLabel' => $start->format('F Y'),
            'startWeekday' => $start->dayOfWeek,
            'today' => $days[$todayKey] ?? null,
            'selectedDay' => $days[$selectedKey] ?? array_values($days)[0] ?? null,
            'days' => array_values($days),
        ];
    }

    protected function databaseNow(): Carbon
    {
        return now()->timezone($this->browserTimezone());
    }

    protected function databaseTime($value): Carbon
    {
        return Carbon::parse($value)
            ->timezone($this->browserTimezone());
    }

    protected function browserTimezone(): string
    {
        return FilamentTimezone::get()
            ?? config('app.display_timezone', 'Asia/Kolkata');
    }

    protected function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        if ($seconds < 60) {
            return '<1m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours < 1) {
            return $minutes . 'm';
        }

        return trim($hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : ''));
    }

    protected function formatTime(Carbon $time): string
    {
        return $time->format('h:i A');
    }
}
