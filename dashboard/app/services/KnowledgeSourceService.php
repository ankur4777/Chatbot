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
    set_time_limit(300);

    \Log::info('KNOWLEDGE IMPORT START');
        $knowledgeSource->update([
            'status' => 'processing',
        ]);

        $response = $this->aiService->importKnowledge([
            'website_id' => $knowledgeSource->website_id,
            'type' => $knowledgeSource->type,
            'source' => storage_path('app/public/' . $knowledgeSource->source),
        ]);
        \Log::info('PYTHON IMPORT RESPONSE RECEIVED');

        if ($response['success'] ?? false) {


            $knowledgeBase = KnowledgeBase::updateOrCreate(

                [
                    'knowledge_source_id' => $knowledgeSource->id,
                ],
                [
                    'website_id' => $knowledgeSource->website_id,
                    'knowledge_category_id' => $knowledgeSource->knowledge_category_id,
                    'title' => $knowledgeSource->title,
                    'content' => $response['content'] ?? '',
                    'source_type' => 'pdf',
                    'source_file' => $knowledgeSource->source,
                ]
            );
            \Log::info('KNOWLEDGE BASE CREATED');

            $knowledgeBase->chunks()->delete();

            foreach ($response['chunk_data'] ?? [] as $chunk) {
                KnowledgeChunk::create([
                    'knowledge_base_id' => $knowledgeBase->id,
                    'website_id' => $knowledgeBase->website_id,
                    'chunk_text' => $chunk['text'],
                    'chunk_order' => $chunk['chunk_index'],
                ]);
            }
            \Log::info('ALL KNOWLEDGE CHUNKS CREATED');
            \Log::info('EMBEDDING SYNC START');

            $embeddingSynced = app(EmbeddingService::class)->sync($knowledgeBase);

            \Log::info('EMBEDDING SYNC FINISHED', [
                'result' => $embeddingSynced,
            ]);

            if ($embeddingSynced === false) {

                $knowledgeSource->update([
                    'status' => 'failed',
                    'error' => 'Failed to create knowledge embeddings or update the vector index.',
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to create knowledge embeddings or update the vector index.',
                ];
            }

            $knowledgeSource->update([
                'status' => 'completed',
                'pages' => $response['pages'] ?? 0,
                'chunks' => $response['chunks'] ?? 0,
                'last_synced_at' => now(),
                'error' => null,
            ]);
            \Log::info('KNOWLEDGE IMPORT COMPLETED');

        } else {

            $knowledgeSource->update([
                'status' => 'failed',
                'error' => $response['message'] ?? 'Unknown error',
            ]);
        }

        return $response;
    }
}