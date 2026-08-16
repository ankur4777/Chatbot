<?php

namespace App\Services;
use App\Models\KnowledgeChunk;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    public function sync(KnowledgeBase $knowledgeBase): bool
    {
        \Log::info('EmbeddingService sync called', [
    'website_id' => $knowledgeBase->website_id,
    'knowledge_base_id' => $knowledgeBase->id,
]);
       $chunks = KnowledgeChunk::where(
        'website_id',
        $knowledgeBase->website_id
    )
    ->orderBy('knowledge_base_id')
    ->orderBy('chunk_order')
    ->get();

        if ($chunks->isEmpty()) {
            return false;
        }

        $payload = [
            'knowledge_base_id' => $knowledgeBase->id,
            'website_id' => $knowledgeBase->website_id,
            'chunks' => $chunks->map(function ($chunk) {

    return [

        'id' => $chunk->id,

        'website_id' => $chunk->website_id,

        'knowledge_base_id' => $chunk->knowledge_base_id,

        'chunk_order' => $chunk->chunk_order,

        'text' => $chunk->chunk_text,

        'title' => optional($chunk->knowledgeBase)->title,

        'source' => optional($chunk->knowledgeBase)->source_file,

        'source_type' => optional($chunk->knowledgeBase)->source_type,

    ];

})->values()->toArray(),
        ];

        $response = Http::timeout(300)
            ->post(
                config('services.python.url') . '/knowledge/sync',
                $payload
            );

        if (! $response->successful()) {
            return false;
        }

        foreach ($chunks as $chunk) {
            $chunk->update([
                'embedding_model'        => 'BAAI/bge-small-en-v1.5',
                'embedding_generated'    => true,
                'embedding_generated_at' => now(),
            ]);
        }

        return true;
    }
}