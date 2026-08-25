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
    ],
]);
}

    public function getWebsiteSettings(Website $website): array
{
    return [
        'chatbot_name'     => $website->settings?->chatbot_name,
        'welcome_message'  => $website->settings?->welcome_message,
        'primary_color'    => $website->settings?->primary_color,
        'position'         => $website->settings?->position,
        'placeholder'      => $website->settings?->placeholder,
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
    // Verify domain only if it is sent
    if ($request->filled('domain') && $website->domain !== $request->domain) {
        return null;
    }

    return $website;
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