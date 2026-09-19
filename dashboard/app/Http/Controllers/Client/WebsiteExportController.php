<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Support\BrowserTime;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebsiteExportController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $owner = $request->user();

        abort_unless(
            $owner?->role === 'owner'
            && $owner->company_id,
            403
        );

        $filename = 'websites-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($owner): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Website',
                'Domain',
                'Widget Key',
                'Active',
                'Created At',
                'Updated At',
            ]);

            Website::query()
                ->where('company_id', $owner->company_id)
                ->orderBy('name')
                ->chunk(100, function ($websites) use ($handle): void {
                    foreach ($websites as $website) {
                        fputcsv($handle, [
                            $website->name,
                            $website->domain,
                            $website->widget_key,
                            $website->status ? 'Yes' : 'No',
                            BrowserTime::format($website->created_at),
                            BrowserTime::format($website->updated_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
