<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    private function pythonRequest()
    {
        return Http::withToken(config('services.python.key'));
    }

    private function handlePythonResponse($response, string $operation): array
{
    if ($response->successful()) {
        return $response->json();
    }

    if ($response->status() === 401) {

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

    return [
        'success' => false,
        'message' => 'AI service returned an unexpected response.',
    ];
}

    public function generateResponse(
    int $websiteId,
    string $message,
    array $history,
    ?string $summary = null,
    array $summaryMessages = []
): array {
    try {

        $response = $this->pythonRequest()
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

        // Successful response
        if ($response->successful()) {

            \Log::info('Python Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $response->json();
        }

        // Authentication error
        if ($response->status() === 401) {

            \Log::error('Python Authentication Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'response' => 'AI service authentication failed.',
            ];
        }

        // Client-side API errors (400-499)
        if ($response->clientError()) {

            \Log::error('Python API Client Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'response' => 'There was a problem processing your request.',
            ];
        }

        // Server-side API errors (500-599)
        if ($response->serverError()) {

            \Log::error('Python API Server Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'response' => 'AI service is temporarily unavailable.',
            ];
        }

        // Unexpected HTTP response
        \Log::error('Unexpected Python API Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'response' => 'AI service returned an unexpected response.',
        ];

    } catch (\Illuminate\Http\Client\ConnectionException $e) {

        \Log::error('Python API Connection Error', [
            'message' => $e->getMessage(),
        ]);

        return [
            'response' => 'Unable to connect to AI service.',
        ];

    } catch (\Throwable $e) {

        \Log::error('Python API Unexpected Exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'response' => 'Something went wrong while processing your request.',
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
}

    public function rebuildKnowledge(int $websiteId): array
{
    try {

        $response = $this->pythonRequest()
            ->asJson()
            ->timeout(300)
            ->post(
                config('services.python.url') . '/knowledge/rebuild',
                [
                    'website_id' => $websiteId,
                ]
            );

        return $this->handlePythonResponse(
            $response,
            'knowledge_rebuild'
        );

    } catch (\Illuminate\Http\Client\ConnectionException $e) {

        \Log::error('Python Connection Error', [
            'operation' => 'knowledge_rebuild',
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Unable to connect to AI service.',
        ];

    } catch (\Throwable $e) {

        \Log::error('Python Exception', [
            'operation' => 'knowledge_rebuild',
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Something went wrong while rebuilding knowledge.',
        ];
    }
}

    public function clearKnowledge(int $websiteId): array
{
    try {

        $response = $this->pythonRequest()
    ->asJson()
    ->retry(
        2,
        1000,
        function ($exception, $request) {
            return $exception instanceof \Illuminate\Http\Client\ConnectionException;
        }
    )
    ->timeout(120)
    ->post(
                config('services.python.url') . '/knowledge/clear',
                [
                    'website_id' => $websiteId,
                ]
            );

        return $this->handlePythonResponse(
            $response,
            'knowledge_clear'
        );

    } catch (\Illuminate\Http\Client\ConnectionException $e) {

        \Log::error('Python Connection Error', [
            'operation' => 'knowledge_clear',
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Unable to connect to AI service.',
        ];

    } catch (\Throwable $e) {

        \Log::error('Python Exception', [
            'operation' => 'knowledge_clear',
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Something went wrong while clearing knowledge.',
        ];
    }
}
}