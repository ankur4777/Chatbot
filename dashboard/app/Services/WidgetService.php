<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Illuminate\Support\Str;
use App\Models\ChatConversation;
use App\Models\ChatbotFlow;
use Illuminate\Support\Facades\DB;
use App\Models\VisitorSession;
use App\Services\LiveChatAvailabilityService;

class WidgetService
{
   public function initializeWidget(Request $request)
{
    $website = $this->verifyWebsite($request);

   if (! $website) {
    return response()->json([
        'success' => false,
        'message' => 'Website not found.'
    ], 404);
}

    return response()->json([
    'success' => true,
    'data' => [
        'website'      => $website,
        'settings'     => $this->getWebsiteSettings($website),
        'realtime' => $this->getRealtimeSettings(),
    ],
]);
}

    public function getWebsiteSettings(Website $website): array
{
        $enableLiveChat = (bool) $website->settings?->enable_live_chat;
        $agentStats = app(LiveChatAvailabilityService::class)
            ->getWebsiteAgentStats($website);
        $offlineBehavior = $website->settings?->offline_behavior
            ?? 'show_offline_form';
        $hasAvailableAgents = $agentStats['available'] > 0;
        $showLiveChatEntry = $enableLiveChat
            && (
                $hasAvailableAgents
                || $offlineBehavior !== 'hide_button'
            );

        return [
        'chatbot_name'     => $website->settings?->chatbot_name,
        'welcome_message'  => $website->settings?->welcome_message,
        'primary_color'    => $website->settings?->primary_color,
        'position'         => $website->settings?->position,
        'placeholder'      => $website->settings?->placeholder,
        'enable_live_chat' => $enableLiveChat,
        'enable_ai_responses' => $website->settings?->enable_ai_responses !== false,
        'show_live_chat_entry' => $showLiveChatEntry,
        'can_request_live_chat' => $showLiveChatEntry,
        'live_chat_available' => $enableLiveChat && $hasAvailableAgents,
        'offline_behavior' => $offlineBehavior,
        'offline_message' => $website->settings?->offline_message
            ?: 'Our support team is currently offline. Leave your details and message and we will get back to you.',
        'waiting_message' => $website->settings?->waiting_message
            ?: 'Connecting you to our support team Please wait...',
    ];
}

public function getRealtimeSettings(): array
{
    return [
        'enabled' => config('broadcasting.default') === 'reverb'
            && filled(config('broadcasting.connections.reverb.key')),
        'broadcaster' => 'reverb',
        'key' => config('broadcasting.connections.reverb.key'),
        'host' => config('broadcasting.connections.reverb.options.host'),
        'port' => (int) config('broadcasting.connections.reverb.options.port'),
        'scheme' => config('broadcasting.connections.reverb.options.scheme'),
        'auth_endpoint' => url('/api/widget/realtime-auth'),
    ];
}

public function verifyWebsite(Request $request){
    
    $website = Website::with([
        'settings',
    ])
    ->where('widget_key', $request->widget_key)
    ->first();

    if (! $website) {
        return null;
    }
 // Website must be active
    if (! $website->status) {
        return null;
    }
    // Verify domain only if it is sent. Ignore scheme/port for local testing.
    if (
        $request->filled('domain') &&
        $this->normalizeDomain($website->domain) !== $this->normalizeDomain($request->domain)
    ) {
        return null;
    }

    return $website;
}

private function normalizeDomain(?string $domain): ?string
{
    if (! $domain) {
        return null;
    }

    $host = parse_url($domain, PHP_URL_HOST);

    if (! $host) {
        $host = parse_url('http://' . $domain, PHP_URL_HOST);
    }

    return strtolower($host ?: $domain);
}

public function findOrCreateVisitor(Request $request, Website $website)
{
    $visitorUuid = $request->visitor_uuid;

    if (! $visitorUuid) {
        $visitorUuid = (string) Str::uuid();
    }

    $visitor = Visitor::firstOrCreate(
        [
            'website_id'   => $website->id,
            'visitor_uuid' => $visitorUuid,
        ],
        [
            'ip_address'       => $request->ip(),
            'first_seen_at'    => now(),
            'last_activity_at' => null,
        ]
    );

    // Only update IP during widget initialization.
    // Do NOT update last_activity_at here.
    $visitor->update([
        'ip_address' => $request->ip(),
    ]);

    return $visitor;
}

public function recordVisitorActivity(
    Request $request,
    Website $website
) {
    $visitorUuid = $request->visitor_uuid;
    $sessionId = $request->session_id;

    if (! $visitorUuid || ! $sessionId) {
        return null;
    }

    // Find existing visitor or create new visitor
    $visitor = Visitor::firstOrCreate(
        [
            'website_id' => $website->id,
            'visitor_uuid' => $visitorUuid,
        ],
        [
            'ip_address' => $request->ip(),
            'first_seen_at' => now(),
            'last_activity_at' => now(),
        ]
    );

    // Create session only when actual chatbot activity happens
    $session = VisitorSession::firstOrCreate(
        [
            'session_id' => $sessionId,
        ],
        [
            'visitor_id' => $visitor->id,
            'ip_address' => $request->ip(),
            'started_at' => now(),
            'last_activity_at' => now(),
        ]
    );

    // Update current session activity
    $session->update([
        'last_activity_at' => now(),
        'ip_address' => $request->ip(),
    ]);

    // Update visitor activity
    $visitor->update([
        'last_activity_at' => now(),
        'ip_address' => $request->ip(),
    ]);

    return $visitor;
}

public function getFlow(Request $request)
{
    $website = $this->verifyWebsite($request);

    if (! $website) {
        return response()->json([
            'success' => false,
            'message' => 'Website not found.',
        ], 404);
    }

    $flow = ChatbotFlow::with([
        'steps.options' => function ($query) {
            $query->orderBy('sort_order');
        }
    ])
    ->where('website_id', $website->id)
    ->where('is_active', true)
    ->first();

    if (! $flow) {
        return response()->json([
            'success' => false,
            'message' => 'No active chatbot flow found.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'flow' => $flow,
    ]);
}
}
