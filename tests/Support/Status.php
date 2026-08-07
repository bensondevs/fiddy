<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum Status: string implements HasDescription, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Published = 'published';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencilSquare,
            self::Published => Heroicon::OutlinedCheckCircle,
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Draft => 'Not visible yet',
            self::Published => 'Live on the site',
        };
    }
}
