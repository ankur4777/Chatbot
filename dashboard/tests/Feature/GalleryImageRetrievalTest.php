<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\Company;
use App\Models\Visitor;
use App\Models\VisitorSession;
use App\Models\Website;
use App\Models\WebsiteGalleryImage;
use App\Models\WebsiteSetting;
use App\Services\AIService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class GalleryImageRetrievalTest extends TestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite is not installed.');
        }

        if (! self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }

        Storage::fake('public');
    }

    public function test_matching_image_request_returns_structured_gallery_results_and_persists_them(): void
    {
        [, $website] = $this->websiteFixture();
        $this->galleryImage($website, [
            'category' => 'kitchen',
            'title' => 'Modern modular kitchen',
            'description' => 'White kitchen with marble countertop',
            'tags' => ['modern', 'modular kitchen', 'marble'],
        ]);
        $this->mockAiResponse(times: 0);

        $response = $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => 'visitor-gallery',
            'session_id' => 'session-gallery',
            'message' => 'Show me modern modular kitchen designs',
        ]);

        $response->assertOk()
            ->assertJsonPath('response_type', 'gallery_results')
            ->assertJsonCount(1, 'gallery_results')
            ->assertJsonPath('gallery_results.0.title', 'Modern modular kitchen');

        $botMessage = ChatMessage::query()
            ->where('sender_type', 'bot')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            'Modern modular kitchen',
            $botMessage->metadata['gallery_results'][0]['title'] ?? null
        );
    }

    public function test_unrelated_text_request_keeps_existing_ai_route(): void
    {
        [, $website] = $this->websiteFixture('Acme', 'plain.test');
        $this->galleryImage($website, [
            'title' => 'Modern modular kitchen',
            'tags' => ['kitchen'],
        ]);
        $this->mockAiResponse(times: 1);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => 'visitor-ai',
            'session_id' => 'session-ai',
            'message' => 'What are your opening hours?',
        ])->assertOk()
            ->assertJsonPath('response', 'AI response')
            ->assertJsonMissingPath('gallery_results.0');
    }

    public function test_category_and_keyword_match_rank_the_most_specific_image_first(): void
    {
        [, $website] = $this->websiteFixture();
        $this->galleryImage($website, [
            'category' => 'ring',
            'title' => 'Round Ring Red',
            'tags' => ['round', 'red'],
        ]);
        $this->galleryImage($website, [
            'category' => 'ring',
            'title' => 'Round Ring Blue',
            'tags' => ['round', 'blue'],
        ]);
        $this->mockAiResponse(times: 0);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => 'visitor-specific-match',
            'session_id' => 'session-specific-match',
            'message' => 'Show me a round ring red image',
        ])->assertOk()
            ->assertJsonPath('response_type', 'gallery_results')
            ->assertJsonCount(1, 'gallery_results')
            ->assertJsonPath('gallery_results.0.title', 'Round Ring Red');
    }

    public function test_unmatched_subject_does_not_return_an_image_from_a_shared_adjective(): void
    {
        [, $website] = $this->websiteFixture();
        $this->galleryImage($website, [
            'category' => 'ring',
            'title' => 'Modern Ring',
            'tags' => ['modern'],
        ]);
        $this->mockAiResponse(times: 0);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => 'visitor-unmatched-subject',
            'session_id' => 'session-unmatched-subject',
            'message' => 'Show me a modern phone image',
        ])->assertOk()
            ->assertJsonPath('response_type', 'gallery_results')
            ->assertJsonCount(0, 'gallery_results');
    }

    public function test_gallery_search_is_scoped_to_verified_website(): void
    {
        [, $website] = $this->websiteFixture('First', 'first.test');
        [, $otherWebsite] = $this->websiteFixture('Second', 'second.test');

        $this->galleryImage($otherWebsite, [
            'title' => 'Modern modular kitchen',
            'description' => 'Marble kitchen design',
            'tags' => ['modern', 'kitchen'],
        ]);
        $this->mockAiResponse(times: 0);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => 'visitor-scope',
            'session_id' => 'session-scope',
            'message' => 'Show me modern kitchen photos',
        ])->assertOk()
            ->assertJsonPath('response_type', 'gallery_results')
            ->assertJsonCount(0, 'gallery_results')
            ->assertJsonPath('response', 'I could not find any matching images in this website gallery.');
    }

    protected function websiteFixture(string $companyName = 'Acme', string $domain = 'example.test'): array
    {
        $company = Company::create([
            'name' => $companyName,
            'email' => $companyName . '@example.test',
            'status' => true,
        ]);

        $website = Website::create([
            'company_id' => $company->id,
            'name' => $companyName . ' Website',
            'domain' => $domain,
            'status' => true,
        ]);

        WebsiteSetting::create([
            'website_id' => $website->id,
            'enable_chatbot' => true,
            'enable_ai_responses' => true,
        ]);

        return [$company, $website->refresh()];
    }

    protected function galleryImage(Website $website, array $overrides = []): WebsiteGalleryImage
    {
        $path = 'gallery-images/' . uniqid('test-', true) . '.jpg';
        Storage::disk('public')->put($path, 'fake image content');

        return WebsiteGalleryImage::create(array_merge([
            'company_id' => $website->company_id,
            'website_id' => $website->id,
            'title' => 'Gallery image',
            'description' => null,
            'tags' => [],
            'image_path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 18,
            'is_active' => true,
        ], $overrides));
    }

    protected function mockAiResponse(int $times): void
    {
        $mock = Mockery::mock(AIService::class);
        $expectation = $mock->shouldReceive('generateResponse');

        if ($times === 0) {
            $expectation->never();
        } else {
            $expectation
                ->times($times)
                ->andReturn([
                    'response' => 'AI response',
                    'summary' => null,
                ]);
        }

        $this->app->instance(AIService::class, $mock);
    }
}
