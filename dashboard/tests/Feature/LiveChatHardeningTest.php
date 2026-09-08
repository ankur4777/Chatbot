<?php

namespace Tests\Feature;

use App\Models\CannedReply;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatbotLead;
use App\Models\Company;
use App\Models\LiveChatSession;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorSession;
use App\Models\Website;
use App\Models\WebsiteSetting;
use App\Notifications\AgentResetPasswordNotification;
use App\Services\AIService;
use App\Services\LiveChatAvailabilityService;
use App\Services\LiveChatService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Mockery;
use Tests\TestCase;

class LiveChatHardeningTest extends TestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped(
                'The configured PHPUnit database uses SQLite, but pdo_sqlite is not installed.'
            );
        }

        if (! self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }
    }

    public function test_normal_ai_conversation_remains_active_ai(): void
    {
        [$company, $website] = $this->websiteFixture();

        $this->mockAiResponse(times: 1);

        $response = $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => 'visitor-one',
            'session_id' => 'session-one',
            'message' => 'Hello',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('mode', 'ai');

        $this->assertDatabaseHas('chat_conversations', [
            'website_id' => $website->id,
            'status' => 'active',
            'mode' => 'ai',
        ]);

        unset($company);
    }

    public function test_request_live_chat_moves_active_conversation_to_waiting_agent(): void
    {
        [$company, $website] = $this->websiteFixture();
        $this->agentFixture($company, [
            'availability_status' => 'online',
            'is_online' => true,
        ]);
        [$visitor, $conversation] = $this->conversationFixture($website);

        $response = $this->postJson('/api/widget/request-live-chat', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'waiting_agent')
            ->assertJsonPath('mode', 'ai');
    }

    public function test_waiting_and_live_messages_do_not_call_ai(): void
    {
        [, $website] = $this->websiteFixture();
        [$visitor, $conversation] = $this->conversationFixture($website, [
            'status' => 'waiting_agent',
            'mode' => 'ai',
            'handoff_requested_at' => now(),
        ]);

        $this->mockAiResponse(times: 0);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'message' => 'Still here',
        ])->assertOk()->assertJsonPath('ai_response', null);

        $conversation->update([
            'status' => 'live_active',
            'mode' => 'live',
        ]);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'message' => 'Live message',
        ])->assertOk()->assertJsonPath('ai_response', null);
    }

    public function test_agent_accept_is_single_assignment_and_cannot_be_stolen(): void
    {
        [$company, $website] = $this->websiteFixture();
        $agent = $this->agentFixture($company, [
            'availability_status' => 'online',
            'is_online' => true,
        ]);
        $secondAgent = $this->agentFixture($company, [
            'email' => 'second-agent@example.test',
            'availability_status' => 'online',
            'is_online' => true,
        ]);
        [, $conversation] = $this->conversationFixture($website, [
            'status' => 'waiting_agent',
            'mode' => 'ai',
        ]);

        $accepted = app(LiveChatService::class)
            ->acceptConversation($conversation, $agent);

        $this->assertSame('live_active', $accepted->status);
        $this->assertSame($agent->id, $accepted->assigned_agent_id);

        $this->expectException(\InvalidArgumentException::class);

        app(LiveChatService::class)
            ->acceptConversation($accepted, $secondAgent);
    }

    public function test_unassigned_or_cross_company_agent_cannot_send_to_live_chat(): void
    {
        [$company, $website] = $this->websiteFixture();
        [$otherCompany] = $this->websiteFixture('Other Co', 'other.test');
        $assignedAgent = $this->agentFixture($company);
        $otherAgent = $this->agentFixture($otherCompany, [
            'email' => 'other-agent@example.test',
        ]);
        [, $conversation] = $this->conversationFixture($website, [
            'status' => 'live_active',
            'mode' => 'live',
            'assigned_agent_id' => $assignedAgent->id,
        ]);

        $this->expectException(AuthorizationException::class);

        app(LiveChatService::class)
            ->sendAgentMessage($conversation, $otherAgent, 'Nope');
    }

    public function test_agent_ends_live_support_without_closing_conversation(): void
    {
        [$company, $website] = $this->websiteFixture();
        $agent = $this->agentFixture($company, [
            'availability_status' => 'online',
            'is_online' => true,
        ]);
        [$visitor, $conversation] = $this->conversationFixture($website, [
            'status' => 'live_active',
            'mode' => 'live',
            'assigned_agent_id' => $agent->id,
            'assigned_at' => now(),
            'handoff_requested_at' => now()->subMinute(),
            'live_started_at' => now(),
        ]);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'message' => 'Before live support ends',
        ]);
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'agent',
            'sender_id' => $agent->id,
            'message' => 'Agent response',
        ]);

        $ended = app(LiveChatService::class)
            ->closeConversationAsAgent($conversation, $agent);

        $this->assertSame($conversation->id, $ended->id);
        $this->assertSame('active', $ended->status);
        $this->assertSame('ai', $ended->mode);
        $this->assertNull($ended->assigned_agent_id);
        $this->assertNull($ended->assigned_at);
        $this->assertNull($ended->handoff_requested_at);
        $this->assertNotNull($ended->live_ended_at);
        $this->assertSame(2, $ended->messages()->count());
        $this->assertDatabaseHas('live_chat_sessions', [
            'conversation_id' => $conversation->id,
            'agent_id' => $agent->id,
            'ended_by' => 'agent',
        ]);

        $this->mockAiResponse(times: 1);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'message' => 'Can AI help again?',
        ])->assertOk()
            ->assertJsonPath('conversation_id', $conversation->id)
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('mode', 'ai')
            ->assertJsonPath('response', 'AI response');

        $this->postJson('/api/widget/request-live-chat', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
        ])->assertOk()
            ->assertJsonPath('conversation_id', $conversation->id)
            ->assertJsonPath('status', 'waiting_agent')
            ->assertJsonPath('mode', 'ai');
    }

    public function test_visitor_ending_live_chat_creates_visitor_closed_session(): void
    {
        [$company, $website] = $this->websiteFixture();
        $agent = $this->agentFixture($company, [
            'availability_status' => 'online',
            'is_online' => true,
        ]);
        [$visitor, $conversation] = $this->conversationFixture($website, [
            'status' => 'live_active',
            'mode' => 'live',
            'assigned_agent_id' => $agent->id,
            'assigned_at' => now(),
            'live_started_at' => now(),
        ]);

        LiveChatSession::create([
            'conversation_id' => $conversation->id,
            'agent_id' => $agent->id,
            'started_at' => now(),
        ]);

        $this->postJson('/api/widget/end-chat', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
        ])->assertOk()
            ->assertJsonPath('status', 'closed');

        $this->assertDatabaseHas('live_chat_sessions', [
            'conversation_id' => $conversation->id,
            'agent_id' => $agent->id,
            'ended_by' => 'visitor',
        ]);
    }

    public function test_ai_only_and_waiting_only_conversations_do_not_have_pending_rating(): void
    {
        [, $website] = $this->websiteFixture();
        [$visitor, $conversation] = $this->conversationFixture($website);

        $this->postJson('/api/widget/live-chat-rating/pending', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
        ])->assertOk()
            ->assertJsonPath('pending_rating', null);

        $conversation->update([
            'status' => 'waiting_agent',
            'mode' => 'ai',
        ]);

        $this->postJson('/api/widget/end-chat', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
        ])->assertOk()
            ->assertJsonPath('pending_rating', null);
    }

    public function test_completed_live_chat_rating_can_be_submitted_only_once(): void
    {
        [$company, $website] = $this->websiteFixture();
        $agent = $this->agentFixture($company, [
            'availability_status' => 'online',
            'is_online' => true,
        ]);
        [$visitor, $conversation] = $this->conversationFixture($website, [
            'status' => 'live_active',
            'mode' => 'live',
            'assigned_agent_id' => $agent->id,
            'assigned_at' => now(),
            'live_started_at' => now(),
        ]);

        LiveChatSession::create([
            'conversation_id' => $conversation->id,
            'agent_id' => $agent->id,
            'started_at' => now()->subMinutes(3),
            'rating_status' => 'pending',
        ]);

        app(LiveChatService::class)
            ->closeConversationAsAgent($conversation, $agent);

        $session = LiveChatSession::where('conversation_id', $conversation->id)
            ->where('agent_id', $agent->id)
            ->firstOrFail();

        $this->postJson('/api/widget/live-chat-rating/submit', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'live_chat_session_id' => $session->id,
            'rating' => 5,
            'feedback' => 'Helpful support',
        ])->assertOk()
            ->assertJsonPath('rating_status', 'submitted');

        $this->assertDatabaseHas('live_chat_sessions', [
            'id' => $session->id,
            'rating_status' => 'submitted',
            'rating' => 5,
            'feedback' => 'Helpful support',
        ]);

        $this->postJson('/api/widget/live-chat-rating/submit', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'live_chat_session_id' => $session->id,
            'rating' => 4,
        ])->assertStatus(409);
    }

    public function test_live_active_new_chat_close_returns_pending_rating(): void
    {
        [$company, $website] = $this->websiteFixture();
        $agent = $this->agentFixture($company);
        [$visitor, $conversation] = $this->conversationFixture($website, [
            'status' => 'live_active',
            'mode' => 'live',
            'assigned_agent_id' => $agent->id,
            'assigned_at' => now(),
            'live_started_at' => now(),
        ]);

        LiveChatSession::create([
            'conversation_id' => $conversation->id,
            'agent_id' => $agent->id,
            'started_at' => now()->subMinutes(2),
            'rating_status' => 'pending',
        ]);

        $response = $this->postJson('/api/widget/end-chat', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
        ])->assertOk()
            ->assertJsonPath('status', 'closed')
            ->assertJsonPath('pending_rating.agent_name', $agent->name);

        $this->assertNotNull(
            $response->json('pending_rating.session_id')
        );
    }

    public function test_agent_closed_chats_show_only_own_completed_live_sessions(): void
    {
        [$company, $website] = $this->websiteFixture();
        $agent = $this->agentFixture($company);
        $otherAgent = $this->agentFixture($company, [
            'email' => 'other-closed@example.test',
        ]);

        [, $aiConversation] = $this->conversationFixture($website, [
            'status' => 'ended',
            'mode' => 'ai',
        ], 'ai-ended-visitor', 'ai-ended-session');

        [, $ownConversation] = $this->conversationFixture($website, [
            'status' => 'active',
            'mode' => 'ai',
        ], 'own-live-history', 'own-live-session');

        [, $otherConversation] = $this->conversationFixture($website, [
            'status' => 'active',
            'mode' => 'ai',
        ], 'other-live-history', 'other-live-session');

        LiveChatSession::create([
            'conversation_id' => $ownConversation->id,
            'agent_id' => $agent->id,
            'started_at' => now()->subMinutes(5),
            'ended_at' => now(),
            'ended_by' => 'agent',
        ]);

        LiveChatSession::create([
            'conversation_id' => $otherConversation->id,
            'agent_id' => $otherAgent->id,
            'started_at' => now()->subMinutes(5),
            'ended_at' => now(),
            'ended_by' => 'visitor',
        ]);

        $this->actingAs($agent)
            ->get('/agent/closed')
            ->assertOk()
            ->assertSee('Closed by Agent')
            ->assertSee('Closed')
            ->assertDontSee('ended')
            ->assertDontSee((string) $aiConversation->id)
            ->assertDontSee((string) $otherConversation->id);
    }

    public function test_only_assigned_agent_can_end_live_support_once(): void
    {
        [$company, $website] = $this->websiteFixture();
        $assignedAgent = $this->agentFixture($company);
        $otherAgent = $this->agentFixture($company, [
            'email' => 'same-company-other@example.test',
        ]);
        [, $conversation] = $this->conversationFixture($website, [
            'status' => 'live_active',
            'mode' => 'live',
            'assigned_agent_id' => $assignedAgent->id,
        ]);

        try {
            app(LiveChatService::class)
                ->closeConversationAsAgent($conversation, $otherAgent);
            $this->fail('Unassigned agent was able to end live support.');
        } catch (AuthorizationException $exception) {
            $this->assertSame(
                'This conversation is not assigned to this agent.',
                $exception->getMessage()
            );
        }

        app(LiveChatService::class)
            ->closeConversationAsAgent($conversation, $assignedAgent);

        $conversation->refresh();

        $this->assertSame('active', $conversation->status);
        $this->assertSame('ai', $conversation->mode);

        $this->expectException(AuthorizationException::class);

        app(LiveChatService::class)
            ->closeConversationAsAgent($conversation, $assignedAgent);
    }

    public function test_closed_conversation_rejects_visitor_messages(): void
    {
        [, $website] = $this->websiteFixture();
        [$visitor, $conversation] = $this->conversationFixture($website, [
            'status' => 'closed',
            'mode' => 'live',
        ]);

        $this->mockAiResponse(times: 0);

        $this->postJson('/api/widget/send-message', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'message' => 'Closed?',
        ])->assertStatus(409)
            ->assertJsonPath('message', 'This conversation has ended.');
    }

    public function test_offline_request_source_is_server_controlled(): void
    {
        [, $website] = $this->websiteFixture();
        [$visitor, $conversation] = $this->conversationFixture($website);

        $this->postJson('/api/widget/offline-live-chat-request', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'name' => 'Visitor',
            'email' => 'visitor@example.test',
            'message' => 'Please help',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('phone');

        $this->postJson('/api/widget/offline-live-chat-request', [
            'widget_key' => $website->widget_key,
            'domain' => $website->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'session-one',
            'conversation_id' => $conversation->id,
            'source' => 'forged',
            'name' => 'Visitor',
            'email' => 'visitor@example.test',
            'phone' => '+1234567890',
            'message' => 'Please help',
        ])->assertOk()
            ->assertJsonPath('message', 'Your request has been submitted successfully.')
            ->assertJsonPath('lead.name', 'Visitor')
            ->assertJsonPath('lead.email', 'visitor@example.test')
            ->assertJsonPath('lead.phone', '+1234567890')
            ->assertJsonPath('lead.message', 'Please help')
            ->assertJsonMissingPath('lead_id');

        $this->assertDatabaseHas('chatbot_leads', [
            'conversation_id' => $conversation->id,
            'source' => 'live_chat_offline_request',
        ]);
    }

    public function test_availability_limit_canned_replies_and_realtime_auth_are_scoped(): void
    {
        [$company, $website] = $this->websiteFixture();
        [$otherCompany, $otherWebsite] = $this->websiteFixture('Other Co', 'other.test');

        $onlineAgent = $this->agentFixture($company, [
            'availability_status' => 'online',
            'is_online' => true,
        ]);
        $this->agentFixture($company, [
            'email' => 'away@example.test',
            'availability_status' => 'away',
        ]);
        $otherAgent = $this->agentFixture($otherCompany, [
            'email' => 'other@example.test',
            'availability_status' => 'online',
            'is_online' => true,
        ]);

        $stats = app(LiveChatAvailabilityService::class)
            ->getCompanyAgentStats($company->id);

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['available']);

        WebsiteSetting::where('website_id', $website->id)
            ->update(['max_active_chats_per_agent' => 1]);

        ChatConversation::create([
            'website_id' => $website->id,
            'visitor_id' => $this->visitorFixture($website, 'limit-visitor')->id,
            'status' => 'live_active',
            'mode' => 'live',
            'assigned_agent_id' => $onlineAgent->id,
        ]);

        [, $waitingConversation] = $this->conversationFixture($website, [
            'status' => 'waiting_agent',
            'mode' => 'ai',
        ], 'waiting-limit-visitor', 'waiting-limit-session');

        try {
            app(LiveChatService::class)
                ->acceptConversation($waitingConversation, $onlineAgent);
            $this->fail('Active chat limit was not enforced.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame(
                'You have reached your active chat limit.',
                $exception->getMessage()
            );
        }

        CannedReply::create([
            'company_id' => $company->id,
            'title' => 'Own',
            'message' => 'Own reply',
        ]);
        CannedReply::create([
            'company_id' => $otherCompany->id,
            'title' => 'Other',
            'message' => 'Other reply',
        ]);

        $this->actingAs($onlineAgent)
            ->get('/agent/chats')
            ->assertOk();

        $this->actingAs($otherAgent)
            ->get('/agent/chats/' . ChatConversation::where('website_id', $website->id)->first()->id)
            ->assertNotFound();

        [$visitor, $conversation] = $this->conversationFixture($otherWebsite, [
            'status' => 'waiting_agent',
            'mode' => 'ai',
        ], 'rt-visitor', 'rt-session');

        $this->postJson('/api/widget/realtime-auth', [
            'widget_key' => $otherWebsite->widget_key,
            'domain' => $otherWebsite->domain,
            'visitor_uuid' => $visitor->visitor_uuid,
            'session_id' => 'wrong-session',
            'conversation_id' => $conversation->id,
            'socket_id' => '1.1',
            'channel_name' => 'private-live-chat.' . $conversation->id,
        ])->assertForbidden();
    }

    public function test_agent_forgot_password_sends_only_agent_reset_notification(): void
    {
        Notification::fake();

        [$company] = $this->websiteFixture();
        $agent = $this->agentFixture($company, [
            'email' => 'reset-agent@example.test',
            'status' => true,
        ]);
        $owner = User::create([
            'company_id' => $company->id,
            'name' => 'Owner',
            'email' => 'owner-reset@example.test',
            'password' => 'password',
            'role' => 'owner',
            'status' => true,
        ]);

        $this->post('/agent/forgot-password', [
            'email' => $owner->email,
        ])->assertSessionHas('status');

        Notification::assertNotSentTo(
            $owner,
            AgentResetPasswordNotification::class
        );

        $this->post('/agent/forgot-password', [
            'email' => $agent->email,
        ])->assertSessionHas('status');

        Notification::assertSentTo(
            $agent,
            AgentResetPasswordNotification::class
        );
    }

    public function test_agent_password_reset_updates_password_without_setting_online(): void
    {
        [$company] = $this->websiteFixture();
        $agent = $this->agentFixture($company, [
            'email' => 'agent-password-reset@example.test',
            'password' => 'old-password',
            'availability_status' => 'offline',
        ]);

        $token = Password::broker()->createToken($agent);

        $this->post('/agent/reset-password', [
            'token' => $token,
            'email' => $agent->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('agent.login'));

        $agent->refresh();

        $this->assertTrue(
            Hash::check('new-password', $agent->password)
        );
        $this->assertSame('offline', $agent->availability_status);
    }

    protected function websiteFixture(
        string $companyName = 'Acme',
        string $domain = 'example.test'
    ): array {
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
            'enable_live_chat' => true,
            'offline_behavior' => 'show_offline_form',
            'max_active_chats_per_agent' => 3,
        ]);

        return [$company, $website->refresh()];
    }

    protected function agentFixture(Company $company, array $overrides = []): User
    {
        return User::create(array_merge([
            'company_id' => $company->id,
            'name' => 'Agent',
            'email' => 'agent-' . uniqid() . '@example.test',
            'password' => 'password',
            'role' => 'agent',
            'status' => true,
            'availability_status' => 'offline',
            'is_online' => false,
        ], $overrides));
    }

    protected function conversationFixture(
        Website $website,
        array $overrides = [],
        string $visitorUuid = 'visitor-one',
        string $sessionId = 'session-one'
    ): array {
        $visitor = $this->visitorFixture($website, $visitorUuid, $sessionId);

        $conversation = ChatConversation::create(array_merge([
            'website_id' => $website->id,
            'visitor_id' => $visitor->id,
            'status' => 'active',
            'mode' => 'ai',
            'started_at' => now(),
        ], $overrides));

        return [$visitor, $conversation];
    }

    protected function visitorFixture(
        Website $website,
        string $visitorUuid,
        string $sessionId = 'session-one'
    ): Visitor {
        $visitor = Visitor::create([
            'website_id' => $website->id,
            'visitor_uuid' => $visitorUuid,
            'first_seen_at' => now(),
            'last_activity_at' => now(),
        ]);

        VisitorSession::create([
            'visitor_id' => $visitor->id,
            'session_id' => $sessionId,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        return $visitor;
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
