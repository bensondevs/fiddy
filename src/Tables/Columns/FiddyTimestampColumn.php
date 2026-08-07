<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tables\Columns;

use Bensondevs\Fiddy\Support\FormatsTimestampContent;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Support\Htmlable;

class FiddyTimestampColumn extends TextColumn
{
    use FormatsTimestampContent;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->dateTime()
            ->html()
            ->wrap()
            ->formatStateUsing(function (FiddyTimestampColumn $column, mixed $state): ?Htmlable {
                return $column->toTimestampHtml($state);
            });
    }
}
