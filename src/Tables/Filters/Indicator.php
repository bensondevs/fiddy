<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tables\Filters;

use BackedEnum;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\OptionGuesser;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Enums\IconSize;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

use function Filament\Support\generate_icon_html;

final class Indicator
{
    protected ?string $title = null;

    protected string | BackedEnum | Htmlable | null $prefixIcon = null;

    protected string | BackedEnum | Htmlable | null $suffixIcon = null;

    public static function make(?string $title = null): self
    {
        $indicator = new self;

        if (filled($title)) {
            $indicator->title($title);
        }

        return $indicator;
    }

    public static function fromEnum(UnitEnum $case): self
    {
        if ($case instanceof FiddyComponentsPresentable) {
            return $case->asFilterIndicator();
        }

        return self::fromEnumContracts($case);
    }

    /**
     * Map Filament HasLabel / HasIcon onto an Indicator (no presentable path).
     */
    public static function fromEnumContracts(UnitEnum $case): self
    {
        $indicator = self::make(OptionGuesser::enumLabel($case));

        if ($case instanceof HasIcon) {
            $icon = $case->getIcon();

            if (filled($icon)) {
                $indicator->icon($icon);
            }
        }

        return $indicator;
    }

    public function title(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function prefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->prefixIcon = $icon;

        return $this;
    }

    public function getPrefixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->prefixIcon;
    }

    public function suffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->suffixIcon = $icon;

        return $this;
    }

    public function getSuffixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->suffixIcon;
    }

    public function icon(string | BackedEnum | Htmlable | null $icon): self
    {
        return $this->prefixIcon($icon);
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->prefixIcon;
    }

    protected function resolveIconHtml(string | BackedEnum | Htmlable | null $icon): ?Htmlable
    {
        if (blank($icon)) {
            return null;
        }

        return generate_icon_html($icon, size: IconSize::ExtraSmall);
    }

    public function render(): string
    {
        $prefixIconHtml = $this->resolveIconHtml($this->prefixIcon);
        $suffixIconHtml = $this->resolveIconHtml($this->suffixIcon);
        $title = $this->title;

        $parts = [];

        if ($prefixIconHtml) {
            $parts[] = '<span class="inline-flex shrink-0 items-center justify-center [&_svg]:block">'
                . $prefixIconHtml->toHtml()
                . '</span>';
        }

        if (filled($title)) {
            $parts[] = '<span class="truncate leading-none">' . e($title) . '</span>';
        }

        if ($suffixIconHtml) {
            $parts[] = '<span class="inline-flex shrink-0 items-center justify-center [&_svg]:block">'
                . $suffixIconHtml->toHtml()
                . '</span>';
        }

        return '<span class="inline-flex min-w-0 max-w-full items-center gap-1 leading-none">'
            . implode('', $parts)
            . '</span>';
    }
}
