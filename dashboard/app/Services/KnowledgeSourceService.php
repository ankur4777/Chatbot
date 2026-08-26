<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Models\KnowledgeBase;

class KnowledgeSourceService
{
    public function __construct(
        protected AIService $aiService,
    ) {
    }

    public function import(KnowledgeSource $knowledgeSource): array
    {
        $type = $this->effectiveType($knowledgeSource);

        $knowledgeSource->update([
            'status' => 'processing',
            'type' => $type,
        ]);

        $source = $type === 'website'
            ? $knowledgeSource->source
            : storage_path('app/public/' . $knowledgeSource->source);

        $response = $this->aiService->importKnowledge([
            'website_id' => $knowledgeSource->website_id,
            'type'   => $type,
            'source' => $source,
        ]);

        if ($response['success'] ?? false) {


            $knowledgeBase = KnowledgeBase::updateOrCreate(
                [
                    'knowledge_source_id' => $knowledgeSource->id,
                ],
                [
                     'website_id'          => $knowledgeSource->website_id,
                    'knowledge_category_id' => $knowledgeSource->knowledge_category_id,
                    'title'                 => $knowledgeSource->title,
                    'content'               => $response['content'] ?? '',
                    'source_type'           => $this->sourceType($type),
                    'source_file'           => $type === 'website'
                        ? null
                        : $knowledgeSource->source,
                    'source_url'            => $type === 'website'
                        ? $knowledgeSource->source
                        : null,
                ]
            );

            $knowledgeBase->chunks()->delete();

            foreach ($response['chunk_data'] ?? [] as $chunk) {
                KnowledgeChunk::create([
                    'knowledge_base_id' => $knowledgeBase->id,
                    'website_id'        => $knowledgeBase->website_id,
                    'chunk_text'        => $chunk['text'],
                    'chunk_order'       => $chunk['chunk_index'],
                ]);
            }
            $embeddingSynced = app(EmbeddingService::class)->sync($knowledgeBase);

if ($embeddingSynced === false) {

    $knowledgeSource->update([
        'status' => 'failed',
        'error'  => 'Failed to create knowledge embeddings or update the vector index.',
    ]);

    return [
        'success' => false,
        'message' => 'Failed to create knowledge embeddings or update the vector index.',
    ];
}

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

    protected function effectiveType(KnowledgeSource $knowledgeSource): string
    {
        $source = (string) $knowledgeSource->source;

        if ($this->isUrl($source)) {
            return 'website';
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'pdf',
            'json' => 'json',
            'docx' => 'docx',
            'txt' => 'txt',
            default => $knowledgeSource->type,
        };
    }

    protected function isUrl(string $source): bool
    {
        return in_array(parse_url($source, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    protected function sourceType(string $type): string
    {
        return match ($type) {
            'website' => 'url',
            default => $type,
        };
    }
}
