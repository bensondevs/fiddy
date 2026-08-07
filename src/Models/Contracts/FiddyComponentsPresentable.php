<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Models\Contracts;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Tables\Filters\Indicator;

interface FiddyComponentsPresentable
{
    public function asOption(): Option;

    public function asColumnContent(): Content;

    public function asEntryContent(): Content;

    public function asFilterIndicator(): Indicator;
}
