<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Support\Facades\FilamentTimezone;

class BrowserTime
{
    public static function format(
        CarbonInterface|string|null $value,
        string $format = 'd M Y, h:i A'
    ): string {
        if (! $value) {
            return 'N/A';
        }

        $timezone = FilamentTimezone::get() ?? 'UTC';

        $date = $value instanceof CarbonInterface
            ? $value->copy()
            : Carbon::parse($value);

        return $date
            ->timezone($timezone)
            ->format($format);
    }
}