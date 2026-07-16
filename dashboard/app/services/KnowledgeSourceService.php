<?php

namespace App\Services;
use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Models\KnowledgeBase;

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

    $knowledgeBase = KnowledgeBase::updateOrCreate(

    [
        'knowledge_source_id' => $knowledgeSource->id,
    ],

    [
        'knowledge_category_id' => $knowledgeSource->knowledge_category_id,
        'title'                 => $knowledgeSource->title,
        'content'               => $response['content'] ?? '',
        'source_type'           => 'pdf',
        'source_file'           => $knowledgeSource->source,
    ]

);
$knowledgeBase->chunks()->delete();

foreach ($response['chunk_data'] ?? [] as $chunk) {

    KnowledgeChunk::create([
        'knowledge_base_id' => $knowledgeBase->id,
        'chunk_text'        => $chunk['text'],
        'chunk_order'       => $chunk['chunk_index'],
    ]);

}

foreach ($response['chunk_data'] ?? [] as $chunk) {

    KnowledgeChunk::create([
        'knowledge_base_id' => $knowledgeBase->id,
        'chunk_text'        => $chunk['text'],
        'chunk_order'       => $chunk['chunk_index'],
    ]);

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
}