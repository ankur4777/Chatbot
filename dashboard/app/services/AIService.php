<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    public function generateResponse(string $message): string
    {
        try {

            $response = Http::timeout(10)->post(
                config('services.python.url') . '/chat',
                [
                    'message' => $message,
                ]
            );

            if ($response->successful()) {
                return $response->json('response');
            }

            return 'Sorry, AI service is currently unavailable.';

        } catch (\Throwable $e) {

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