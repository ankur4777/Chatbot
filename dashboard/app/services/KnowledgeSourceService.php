<?php

namespace App\Services;

use App\Models\KnowledgeSource;

class KnowledgeSourceService
{
    public function __construct(
        protected AIService $aiService,
    ) {}

   public function import(KnowledgeSource $knowledgeSource): array
{
    $knowledgeSource->update([
        'status' => 'processing',
    ]);

    $response = $this->aiService->importKnowledge([
        'type'   => $knowledgeSource->type,
        'source' => storage_path('app/public/' . $knowledgeSource->source),
    ]);

    if ($response['success'] ?? false) {

        $knowledgeSource->update([
            'status'         => 'completed',
            'pages'          => $response['pages'] ?? 0,
            'chunks'         => $response['chunks'] ?? 0,
            'last_synced_at' => now(),
            'error'          => null,
        ]);

    } else {

        $knowledgeSource->update([
            'status' => 'failed',
            'error'  => $response['message'] ?? 'Unknown error',
        ]);
    }

    return $response;
}
}