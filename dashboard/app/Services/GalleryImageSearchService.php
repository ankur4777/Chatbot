<?php

namespace App\Services;

use App\Models\Website;
use App\Models\WebsiteGalleryImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GalleryImageSearchService
{
    private const INTENT_WORDS = [
        'image',
        'images',
        'photo',
        'photos',
        'picture',
        'pictures',
        'pic',
        'pics',
        'gallery',
        'design',
        'designs',
        'example',
        'examples',
        'show',
        'see',
        'view',
        'look',
        'display',
        'design',
        'designs',
        'example',
        'examples',
    ];

    private const STOP_WORDS = [
        'a',
        'an',
        'and',
        'are',
        'can',
        'could',
        'do',
        'for',
        'have',
        'i',
        'is',
        'me',
        'of',
        'please',
        'show',
        'the',
        'to',
        'you',
        'your',
        'image',
        'images',
        'photo',
        'photos',
        'picture',
        'pictures',
        'pic',
        'pics',
        'gallery',
        'see',
        'view',
        'display',
    ];

    public function explicitImageRequest(string $message): bool
    {
        $tokens = $this->tokens($message, includeGeneric: true);

        return collect($tokens)->contains(
            fn (string $token): bool => in_array($token, self::INTENT_WORDS, true)
        );
    }

    public function search(Website $website, string $message): Collection
    {
        if (! $this->explicitImageRequest($message)) {
            return collect();
        }

        $terms = $this->tokens($message);

        if ($terms === []) {
            return collect();
        }

        $images = WebsiteGalleryImage::query()
            ->where('company_id', $website->company_id)
            ->where('website_id', $website->id)
            ->where('is_active', true)
            ->latest('updated_at')
            ->limit(100)
            ->get();

        $minScore = max(1, (int) config('gallery.min_score', 2));
        $maxResults = max(1, (int) config('gallery.max_results', 3));
        $categoryTerms = $this->categoryTerms($images, $terms);

        $scoredImages = $images
            ->map(function (WebsiteGalleryImage $image) use ($terms) {
                [$score, $matchedTerms, $categoryMatches] = $this->score($image, $terms);
                $image->setAttribute('gallery_search_score', $score);
                $image->setAttribute('gallery_matched_terms', $matchedTerms);
                $image->setAttribute('gallery_category_matches', $categoryMatches);

                return $image;
            })
            ->filter(fn (WebsiteGalleryImage $image): bool =>
                $image->gallery_search_score >= $minScore
                && ($categoryTerms === [] || $image->gallery_category_matches > 0)
            );

        if ($categoryTerms !== []) {
            $maxMatchedTerms = $scoredImages->max('gallery_matched_terms');

            $scoredImages = $scoredImages->filter(
                fn (WebsiteGalleryImage $image): bool =>
                    $image->gallery_matched_terms === $maxMatchedTerms
            );
        } elseif (count($terms) > 1) {
            // Without a category signal, do not return an image for only a generic
            // adjective such as "modern" when the requested subject is absent.
            $scoredImages = $scoredImages->filter(
                fn (WebsiteGalleryImage $image): bool =>
                    $image->gallery_matched_terms === count($terms)
            );
        }

        return $scoredImages
            ->sortBy([
                ['gallery_category_matches', 'desc'],
                ['gallery_matched_terms', 'desc'],
                ['gallery_search_score', 'desc'],
            ])
            ->take($maxResults)
            ->values();
    }

    protected function categoryTerms(Collection $images, array $terms): array
    {
        return collect($terms)
            ->filter(function (string $term) use ($images): bool {
                return $images->contains(
                    fn (WebsiteGalleryImage $image): bool =>
                        $this->containsWord((string) $image->category, $term)
                );
            })
            ->values()
            ->all();
    }

    protected function score(WebsiteGalleryImage $image, array $terms): array
    {
        $category = Str::lower((string) ($image->category ?? ''));
        $title = Str::lower($image->title ?? '');
        $description = Str::lower($image->description ?? '');
        $tags = collect($image->tags ?? [])
            ->map(fn ($tag): string => Str::lower((string) $tag))
            ->all();

        $score = 0;
        $matchedTerms = 0;
        $categoryMatches = 0;

        foreach ($terms as $term) {
            $termMatched = false;

            if ($this->containsWord($category, $term)) {
                $score += 1000;
                $categoryMatches++;
                $termMatched = true;
            }

            if ($this->containsWord($title, $term)) {
                $score += 40;
                $termMatched = true;
            }

            if ($this->containsWord($description, $term)) {
                $score += 10;
                $termMatched = true;
            }

            foreach ($tags as $tag) {
                if ($this->containsWord($tag, $term)) {
                    $score += 30;
                    $termMatched = true;
                    break;
                }
            }

            if ($termMatched) {
                $matchedTerms++;
            }
        }

        return [$score, $matchedTerms, $categoryMatches];
    }

    protected function containsWord(string $value, string $term): bool
    {
        return preg_match(
            '/(?<![\pL\pN])' . preg_quote($term, '/') . '(?![\pL\pN])/u',
            Str::lower($value)
        ) === 1;
    }

    protected function tokens(string $message, bool $includeGeneric = false): array
    {
        preg_match_all('/[\pL\pN]+/u', Str::lower($message), $matches);

        return collect($matches[0] ?? [])
            ->map(fn (string $token): string => trim($token))
            ->filter(fn (string $token): bool => strlen($token) >= 2)
            ->reject(fn (string $token): bool => ! $includeGeneric && in_array($token, self::STOP_WORDS, true))
            ->unique()
            ->values()
            ->all();
    }
}
