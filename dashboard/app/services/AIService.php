<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
   public function generateResponse(
    string $message,
    array $history
): string
{
    try {

        $response = Http::timeout(120)->post(
            config('services.python.url') . '/chat',
            [
                'message' => $message,
                'history' => $history,
            ]
        );

        if ($response->successful()) {

    \Log::info('Python Response', [
        'status' => $response->status(),
        'body'   => $response->body(),
        'json'   => $response->json(),
    ]);

    return $response->json('response');
}
        return 'Sorry, AI service is currently unavailable.';

    } catch (\Throwable $e) {

    \Log::error('Python Exception', [
        'message' => $e->getMessage(),
        'trace'   => $e->getTraceAsString(),
    ]);

    return 'Unable to connect to AI server.';
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
}