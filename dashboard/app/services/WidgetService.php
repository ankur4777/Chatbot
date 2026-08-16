<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Illuminate\Support\Str;
use App\Models\ChatConversation;
use App\Models\ChatbotFlow;
use Illuminate\Support\Facades\DB;

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
$visitor = $this->findOrCreateVisitor($request, $website);


    return response()->json([
    'success' => true,
    'data' => [
        'website'      => $website,
        'settings'     => $this->getWebsiteSettings($website),
        'quickReplies' => $this->getQuickReplies($website),
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

    public function getQuickReplies(Website $website)
{
    return $website->quickReplies
        ->map(function ($reply) {
            return [
                'label' => $reply->label,
                'value' => $reply->value,
                'icon'  => $reply->icon,
            ];
        })
        ->values();
}
public function verifyWebsite(Request $request){
    
    $website = Website::with([
        'settings',
        'quickReplies' => function ($query) {
            $query->where('is_active', true)
                  ->orderBy('sort_order');
        }
    ])
    ->where('widget_key', $request->widget_key)
    ->first();

    if (! $website) {
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
    
$sessionId = $request->session_id ?? Str::uuid();
$visitor = Visitor::firstOrCreate(
    [
        'session_id' => $sessionId,
    ],
    [
        'website_id' => $website->id,
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]
);
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