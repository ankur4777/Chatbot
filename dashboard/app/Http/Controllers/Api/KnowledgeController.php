<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeSource;
use App\Models\Website;
use App\Models\KnowledgeChunk;

class KnowledgeController extends Controller
{
    public function knowledge(Website $website)
    {
        $sources = KnowledgeSource::where('website_id', $website->id)
            ->where('status', 'completed')
            ->get();

        return response()->json([
            'website_id' => $website->id,
            'sources' => $sources->map(function ($source) {
                return [
                    'id' => $source->id,
                    'type' => $source->type,
                    'title' => $source->title,
                    'source' => storage_path(
                        'app/public/' . $source->source
                    ),
                ];
            })->values(),
        ]);
    }
    public function chunks(Website $website)
{
    $chunks = KnowledgeChunk::where('website_id', $website->id)
        ->orderBy('id')
        ->get([
            'id',
            'chunk_text',
            'chunk_order',
        ]);

    return response()->json([
        'success' => true,
        'website_id' => $website->id,
        'chunks' => $chunks->map(function ($chunk) {
            return [
                'id' => $chunk->id,
                'text' => $chunk->chunk_text,
                'order' => $chunk->chunk_order,
            ];
        })->values(),
    ]);
}
}