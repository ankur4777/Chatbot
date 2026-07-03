<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    public function generateResponse(string $message): string
    {
        $response = Http::post(
    config('services.python.url') . '/chat',
    [
        'message' => $message,
    ]
);

        if ($response->successful()) {
            return $response->json('response');
        }

        return 'Sorry, AI service is currently unavailable.';
    }
}