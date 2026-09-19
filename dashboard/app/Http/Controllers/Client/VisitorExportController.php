<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\Website;
use App\Support\BrowserTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitorExportController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $owner = $request->user();

        abort_unless(
            $owner?->role === 'owner'
            && $owner->company_id,
            403
        );

        $websiteId = $request->integer('website') ?: null;

        if ($websiteId) {
            abort_unless(
                Website::query()
                    ->whereKey($websiteId)
                    ->where('company_id', $owner->company_id)
                    ->exists(),
                404
            );
        }

        $filename = 'visitors-last-30-days-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($owner, $websiteId): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Visitor',
                'Website',
                'IP Address',
                'Total Visits',
                'First Seen',
                'Last Activity',
            ]);

            Visitor::query()
                ->with('website')
                ->withCount('sessions')
                ->whereNotNull('visitor_uuid')
                ->where('visitor_uuid', '!=', '')
                ->where('last_activity_at', '>=', now()->subDays(30))
                ->whereHas(
                    'website',
                    fn (Builder $query) => $query->where(
                        'company_id',
                        $owner->company_id
                    )
                )
                ->when(
                    $websiteId,
                    fn (Builder $query) => $query->where(
                        'website_id',
                        $websiteId
                    )
                )
                ->whereIn('id', function ($subQuery) use ($websiteId): void {
                    $subQuery
                        ->from('visitors')
                        ->selectRaw('MAX(id)')
                        ->whereNotNull('visitor_uuid')
                        ->where('visitor_uuid', '!=', '')
                        ->where('last_activity_at', '>=', now()->subDays(30))
                        ->when(
                            $websiteId,
                            fn ($query) => $query->where(
                                'website_id',
                                $websiteId
                            )
                        )
                        ->groupBy('website_id', 'visitor_uuid');
                })
                ->orderByDesc('last_activity_at')
                ->chunk(100, function ($visitors) use ($handle): void {
                    foreach ($visitors as $visitor) {
                        fputcsv($handle, [
                            'Visitor ' . substr($visitor->visitor_uuid, 0, 8),
                            $visitor->website?->name ?? 'N/A',
                            $visitor->ip_address ?? 'N/A',
                            (int) $visitor->sessions_count,
                            BrowserTime::format(
                                $visitor->created_at,
                                'd M Y, h:i A'
                            ),
                            BrowserTime::format(
                                $visitor->last_activity_at,
                                'd M Y, h:i A'
                            ),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
