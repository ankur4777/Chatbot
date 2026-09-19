<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatbotLead;
use App\Models\Website;
use App\Support\BrowserTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotLeadExportController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $owner = $request->user();

        abort_unless(
            $owner?->role === 'owner' && $owner->company_id,
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

        $filename = 'chatbot-leads-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($owner, $websiteId): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Lead ID',
                'Website',
                'Visitor',
                'Conversation ID',
                'Source',
                'Name',
                'Email',
                'Phone',
                'Message',
                'Created At',
            ]);

            ChatbotLead::query()
                ->with(['website', 'visitor'])
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('source')
                        ->orWhereNotIn('source', [
                            'live_chat_offline_request',
                            'live_chat_assigned_request',
                        ]);
                })
                ->whereHas(
                    'website',
                    fn (Builder $query) => $query->where(
                        'company_id',
                        $owner->company_id
                    )
                )
                ->when(
                    $websiteId,
                    fn (Builder $query) => $query->where('website_id', $websiteId)
                )
                ->latest('created_at')
                ->chunk(100, function ($leads) use ($handle): void {
                    foreach ($leads as $lead) {
                        fputcsv($handle, [
                            $lead->id,
                            $lead->website?->name ?? 'N/A',
                            $lead->visitor?->visitor_uuid
                                ? 'Visitor ' . substr($lead->visitor->visitor_uuid, 0, 8)
                                : 'N/A',
                            $lead->conversation_id ?: 'N/A',
                            $lead->source ?: 'Chatbot',
                            $lead->name ?: 'N/A',
                            $lead->email ?: 'N/A',
                            $lead->phone ?: 'N/A',
                            $lead->notes ?: 'N/A',
                            BrowserTime::format($lead->created_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadMissedChats(Request $request): StreamedResponse
    {
        $owner = $request->user();

        abort_unless(
            $owner?->role === 'owner' && $owner->company_id,
            403
        );

        $websiteId = $request->integer('website') ?: null;

        abort_if(! $websiteId, 404);

        abort_unless(
            Website::query()
                ->whereKey($websiteId)
                ->where('company_id', $owner->company_id)
                ->exists(),
            404
        );

        $filename = 'missed-chats-' . now()->format('Y-m-d-His') . '.csv';
        $cutoff = now()->subDays(30);

        return response()->streamDownload(function () use ($owner, $websiteId, $cutoff): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Missed Chat ID',
                'Website',
                'Visitor',
                'Name',
                'Email',
                'Phone',
                'Message',
                'Assigned Agent',
                'Follow-up Status',
                'Assigned At',
                'Agent Note',
                'Last Contacted At',
                'Next Follow-up At',
                'Resolved At',
                'Requested At',
                'Last Updated',
            ]);

            ChatbotLead::query()
                ->with(['website', 'visitor', 'assignedAgent'])
                ->where('source', 'live_chat_offline_request')
                ->where('website_id', $websiteId)
                ->where('created_at', '>=', $cutoff)
                ->whereHas(
                    'website',
                    fn (Builder $query) => $query->where(
                        'company_id',
                        $owner->company_id
                    )
                )
                ->latest('created_at')
                ->chunk(100, function ($leads) use ($handle): void {
                    foreach ($leads as $lead) {
                        fputcsv($handle, [
                            $lead->id,
                            $lead->website?->name ?? 'N/A',
                            $lead->visitor?->visitor_uuid
                                ? 'Visitor ' . substr($lead->visitor->visitor_uuid, 0, 8)
                                : 'N/A',
                            $lead->name ?: 'N/A',
                            $lead->email ?: 'N/A',
                            $lead->phone ?: 'N/A',
                            $lead->notes ?: 'N/A',
                            $lead->assignedAgent?->name ?? 'Unassigned',
                            $lead->followupStatusLabel(),
                            $lead->assigned_at ? BrowserTime::format($lead->assigned_at) : 'N/A',
                            $lead->agent_note ?: 'N/A',
                            $lead->last_contacted_at ? BrowserTime::format($lead->last_contacted_at) : 'N/A',
                            $lead->next_followup_at ? BrowserTime::format($lead->next_followup_at) : 'N/A',
                            $lead->resolved_at ? BrowserTime::format($lead->resolved_at) : 'N/A',
                            BrowserTime::format($lead->created_at),
                            BrowserTime::format($lead->updated_at),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
