<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\BrowserTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentExportController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $owner = $request->user();

        abort_unless(
            $owner?->role === 'owner'
            && $owner->company_id
            && $owner->hasLiveChatAccess(),
            403
        );

        $filename = 'agents-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($owner): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Name',
                'Email',
                'Phone',
                'Websites',
                'Active',
                'Availability',
                'Avg. Rating',
                'Total Ratings',
                'Last Seen',
                'Created At',
            ]);

            User::query()
                ->with('assignedWebsites')
                ->withAvg([
                    'liveChatSessions as submitted_rating_average' =>
                        fn (Builder $query) => $this->submittedRatingScope(
                            $query,
                            $owner->company_id
                        ),
                ], 'rating')
                ->withCount([
                    'liveChatSessions as submitted_rating_count' =>
                        fn (Builder $query) => $this->submittedRatingScope(
                            $query,
                            $owner->company_id
                        ),
                ])
                ->where('role', 'agent')
                ->where('company_id', $owner->company_id)
                ->orderBy('name')
                ->chunk(100, function ($agents) use ($handle): void {
                    foreach ($agents as $agent) {
                        fputcsv($handle, [
                            $agent->name,
                            $agent->email,
                            $agent->phone ?: 'N/A',
                            $agent->assignedWebsites
                                ->pluck('name')
                                ->implode(', '),
                            $agent->status ? 'Yes' : 'No',
                            match ($agent->availability_status) {
                                'online' => 'Online',
                                'away' => 'On Break',
                                default => 'Offline',
                            },
                            $this->formatAverageRating($agent),
                            (int) ($agent->submitted_rating_count ?? 0),
                            $agent->last_seen_at
                                ? BrowserTime::format($agent->last_seen_at)
                                : 'Never',
                            BrowserTime::format($agent->created_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function submittedRatingScope(
        Builder $query,
        int $companyId
    ): Builder {
        return $query
            ->whereNotNull('ended_at')
            ->where('rating_status', 'submitted')
            ->whereNotNull('rating')
            ->whereHas(
                'conversation.website',
                fn (Builder $query) => $query->where('company_id', $companyId)
            );
    }

    protected function formatAverageRating(User $agent): string
    {
        $count = (int) ($agent->submitted_rating_count ?? 0);

        if ($count < 1 || $agent->submitted_rating_average === null) {
            return 'Not Rated';
        }

        return number_format((float) $agent->submitted_rating_average, 1)
            . ' / 5';
    }
}
