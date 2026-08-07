<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Infolists\Components;

use Bensondevs\Fiddy\Support\FormatsTimestampContent;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Contracts\Support\Htmlable;

class FiddyTimestampEntry extends TextEntry
{
    use FormatsTimestampContent;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->dateTime()
            ->html()
            ->formatStateUsing(function (FiddyTimestampEntry $entry, mixed $state): ?Htmlable {
                return $entry->toTimestampHtml($state);
            });
    }
}
