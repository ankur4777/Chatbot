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
    ): array {

        try {

            $response = Http::withHeaders([
    'Authorization' => 'Bearer ' . env('PYTHON_API_KEY'),
    'Accept' => 'application/json',
])
    ->asJson()
    ->timeout(120)
                ->post(
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
                    'body' => $response->body(),
                    'json' => $response->json(),
                ]);

                return $response->json();
            }

            \Log::error('Python API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'response' => 'Sorry, AI service is currently unavailable.',
            ];

        } catch (\Throwable $e) {

            \Log::error('Python Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'response' => 'Unable to connect to AI server.',
            ];
        }
    }


    public function importKnowledge(array $data): array
    {
        try {

          $response = Http::withHeaders([
    'Authorization' => 'Bearer ' . env('PYTHON_API_KEY'),
    'Accept' => 'application/json',
])
    ->asJson()
    ->timeout(120)
    ->post(
        config('services.python.url') . '/knowledge/import',
        $data
    );

            $result = $response->json();

if (! is_array($result)) {
    \Log::error('Invalid Python API response', [
        'status' => $response->status(),
        'body' => $response->body(),
        'headers' => $response->headers(),
    ]);

    return [
        'success' => false,
        'message' => 'Invalid response from Python API. Status: '
            . $response->status()
            . ' Body: '
            . substr($response->body(), 0, 500),
    ];
}

return $result;

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

          $response = Http::withHeaders([
    'Authorization' => 'Bearer ' . env('PYTHON_API_KEY'),
    'Accept' => 'application/json',
])
    ->asJson()
    ->timeout(300)
    ->post(
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

            $response = Http::withHeaders([
    'Authorization' => 'Bearer ' . env('PYTHON_API_KEY'),
    'Accept' => 'application/json',
])
    ->asJson()
    ->timeout(60)
    ->post(
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
