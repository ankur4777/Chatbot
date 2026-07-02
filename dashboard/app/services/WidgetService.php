<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Http\Request;

class WidgetService
{
   public function initializeWidget(Request $request)
{
    $website = $this->verifyWebsite($request);

    if (! $website) {
       return response()->json([
    'success' => true,

    'data' => [

        'website' => [
            'id'     => $website->id,
            'name'   => $website->name,
            'domain' => $website->domain,
        ],

        'settings' => $this->getWebsiteSettings($website),

        'quick_replies' => $this->getQuickReplies($website),

    ],
]);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'website' => $website,
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

   public function verifyWebsite(Request $request)
{
    return Website::with([
        'settings',
        'quickReplies' => function ($query) {
            $query->where('is_active', true)
                  ->orderBy('sort_order');
        }
    ])
    ->where('domain', $request->domain)
    ->first();
}
}