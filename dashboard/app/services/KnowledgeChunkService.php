<?php

namespace App\Services;
use App\Services\AIService;
use App\Services\EmbeddingService;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeChunk;
class KnowledgeChunkService
{
    private int $maxWords = 450;
    private int $overlapSentences = 2;

    public function generate(KnowledgeBase $knowledgeBase): void
    {
        $knowledgeBase->chunks()->delete();

        $content = trim(strip_tags($knowledgeBase->content));

        if (blank($content)) {
            return;
        }

        // Split into complete sentences
        $sentences = preg_split(
            '/(?<=[.?!])\s+/u',
            preg_replace('/\s+/', ' ', $content),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $chunks = [];
        $currentChunk = [];
        $currentWords = 0;

        foreach ($sentences as $sentence) {

            $sentenceWords = str_word_count($sentence);

            if (
                $currentWords + $sentenceWords > $this->maxWords &&
                ! empty($currentChunk)
            ) {

                $chunks[] = implode(' ', $currentChunk);

                // Keep last 2 sentences as overlap
                $currentChunk = array_slice(
                    $currentChunk,
                    -$this->overlapSentences
                );

                $currentWords = str_word_count(
                    implode(' ', $currentChunk)
                );
            }

            $currentChunk[] = trim($sentence);
            $currentWords += $sentenceWords;
        }

        if (! empty($currentChunk)) {
            $chunks[] = implode(' ', $currentChunk);
        }

        foreach ($chunks as $index => $chunk) {

           $chunk = KnowledgeChunk::create([
                'knowledge_base_id' => $knowledgeBase->id,
                'website_id'        => $knowledgeBase->website_id,
                'chunk_order'       => $index + 1,
                'chunk_text'        => $chunk,
            ]);
            
        }
        app(EmbeddingService::class)->sync($knowledgeBase);
    }
}