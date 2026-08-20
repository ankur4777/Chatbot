<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
   public function generateResponse(
    int $websiteId,
    string $message,
    array $history,
    ?string $summary = null,
    array $summaryMessages = []
): array
{
    try {
        $response = Http::timeout(120)->post(
            config('services.python.url') . '/chat',
            
                [
    'website_id' => $websiteId,
    'message' => $message,
    'history' => $history,
    'summary' => $summary,
    'summary_messages' => $summaryMessages,

            ]
        );

        if ($response->successful()) {

    \Log::info('Python Response', [
        'status' => $response->status(),
        'body'   => $response->body(),
        'json'   => $response->json(),
    ]);

    return $response->json();
}
return [
    'response' => 'Sorry, AI service is currently unavailable.',
];
    } catch (\Throwable $e) {

    \Log::error('Python Exception', [
        'message' => $e->getMessage(),
        'trace'   => $e->getTraceAsString(),
    ]);

return [
    'response' => 'Unable to connect to AI server.',
];
}
}

    public function importKnowledge(array $data): array
    {
        try {

            $response = Http::timeout(120)->post(
    config('services.python.url') . '/knowledge/import',
    $data
);

return $response->json();

        } catch (\Throwable $e) {

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function rebuildKnowledge(int $websiteId): array
{
    try {

        $response = Http::timeout(300)->post(
            config('services.python.url') . '/knowledge/rebuild',
            [
                'website_id' => $websiteId,
            ]
        );

        return $response->json();

    } catch (\Throwable $e) {

        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}
public function clearKnowledge(int $websiteId): array
{
    try {

        $response = Http::timeout(60)->post(
            config('services.python.url') . '/knowledge/clear',
            [
                'website_id' => $websiteId,
            ]
        );

        return $response->json();

    } catch (\Throwable $e) {

        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}
}