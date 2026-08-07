<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

trait ResolvesDateDifferenceHint
{
    public function resolveDifferenceHint(
        mixed $state,
        ?string $timezone = null,
        bool $startOfDay = true,
    ): ?string {
        if (blank($state)) {
            return null;
        }

        $date = Carbon::parse($state);
        $now = now();

        if (filled($timezone)) {
            $date = $date->timezone($timezone);
            $now = $now->timezone($timezone);
        }

        if ($startOfDay) {
            $date = $date->startOfDay();
            $now = $now->startOfDay();
        }

        return $date->diffForHumans(
            $now,
            CarbonInterface::DIFF_RELATIVE_TO_NOW,
        );
    }
}
