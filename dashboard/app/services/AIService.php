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

        \Log::error('Python Authentication Error', [
            'operation' => $operation,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'success' => false,
            'message' => 'AI service authentication failed.',
        ];
    }

    if ($response->clientError()) {

        \Log::error('Python API Client Error', [
            'operation' => $operation,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'success' => false,
            'message' => 'Invalid request sent to AI service.',
        ];
    }

    if ($response->serverError()) {

        \Log::error('Python API Server Error', [
            'operation' => $operation,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'success' => false,
            'message' => 'AI service is temporarily unavailable.',
        ];
    }

    \Log::error('Unexpected Python API Response', [
        'operation' => $operation,
        'status' => $response->status(),
        'body' => $response->body(),
    ]);

    return $response->json();
}
return [
    'response' => 'Sorry, AI service is currently unavailable.',
];
    } catch (\Throwable $e) {

        \Log::error('Python API Unexpected Exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

return [
    'response' => 'Unable to connect to AI server.',
];
}
}

    public function importKnowledge(array $data): array
{
    try {

        $response = $this->pythonRequest()
            ->asJson()
            ->timeout(300)
            ->post(
                config('services.python.url') . '/knowledge/import',
                $data
            );

        return $this->handlePythonResponse(
            $response,
            'knowledge_import'
        );

    } catch (\Illuminate\Http\Client\ConnectionException $e) {

        \Log::error('Python Connection Error', [
            'operation' => 'knowledge_import',
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Unable to connect to AI service.',
        ];

    } catch (\Throwable $e) {

        \Log::error('Python Exception', [
            'operation' => 'knowledge_import',
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Something went wrong while importing knowledge.',
        ];
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