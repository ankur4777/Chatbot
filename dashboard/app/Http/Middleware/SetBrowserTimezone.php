<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetBrowserTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = config('app.display_timezone', 'Asia/Kolkata');

        if (isset($_COOKIE['browser_timezone'])) {
            $timezone = urldecode($_COOKIE['browser_timezone']);
        }

        // Browser may return old timezone aliases.
        $aliases = [
            'Asia/Calcutta' => 'Asia/Kolkata',
            'US/Eastern' => 'America/New_York',
            'US/Central' => 'America/Chicago',
            'US/Mountain' => 'America/Denver',
            'US/Pacific' => 'America/Los_Angeles',
        ];

        $timezone = $aliases[$timezone] ?? $timezone;

        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = config('app.display_timezone', 'Asia/Kolkata');
        }

        FilamentTimezone::set($timezone);

        return $next($request);
    }
}
