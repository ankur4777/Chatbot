<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AgentDashboardLogoController extends Controller
{
    public static function isLogoPath(string $path): bool
    {
        return preg_match('#^live-chat/logos/[A-Za-z0-9_-]+\.[A-Za-z0-9]+$#D', $path) === 1;
    }

    public function show(Request $request, WebsiteSetting $setting): BinaryFileResponse
    {
        $owner = $request->user('client');

        abort_unless($owner?->role === 'owner' && $owner->status
            && ! $owner->must_change_password && $owner->company_id
            && $owner->company?->hasActiveSubscription(), 403);

        abort_unless($setting->website()
            ->where('company_id', $owner->company_id)->exists(), 404);
        abort_unless($setting->enable_live_chat, 403);

        $path = $setting->agent_dashboard_logo;
        $disk = Storage::disk('public');

        abort_unless(is_string($path) && self::isLogoPath($path) && $disk->exists($path), 404);

        $mime = $disk->mimeType($path);
        abort_unless(in_array($mime, [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml',
        ], true), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
