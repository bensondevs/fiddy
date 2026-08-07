<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Tables\Filters\Indicator;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PresentableStatus: string implements FiddyComponentsPresentable, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Approved => Heroicon::OutlinedCheckBadge,
        };
    }

    public function asOption(): Option
    {
        return Option::make($this->value)
            ->title($this->getLabel())
            ->icon($this->getIcon())
            ->description('Presented: ' . $this->getLabel());
    }

    public function asColumnContent(): Content
    {
        return Content::fromEnumContracts($this)
            ->description('Column: ' . $this->getLabel());
    }

    public function asEntryContent(): Content
    {
        return Content::fromEnumContracts($this)
            ->description('Entry: ' . $this->getLabel());
    }

    public function asFilterIndicator(): Indicator
    {
        return Indicator::fromEnumContracts($this)
            ->title('Chip: ' . $this->getLabel());
    }
}
