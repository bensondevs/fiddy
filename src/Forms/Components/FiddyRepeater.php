<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Forms\Components;

use BackedEnum;
use Bensondevs\Fiddy\Support\Content;
use Closure;
use Filament\Forms\Components\Repeater;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class FiddyRepeater extends Repeater
{
    protected string | Closure | null $itemDescription = null;

    protected string | BackedEnum | Htmlable | Closure | null $itemPrefixIcon = null;

    protected string | BackedEnum | Htmlable | Closure | null $itemSuffixIcon = null;

    /**
     * @var string | array<mixed> | Closure | null
     */
    protected string | array | Closure | null $itemIconColor = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->truncateItemLabel(false)
            ->extraAttributes(fn (): array => [
                'class' => 'fiddy-repeater',
            ], merge: true);
    }

    public function itemDescription(string | Closure | null $description): static
    {
        $this->itemDescription = $description;

        return $this;
    }

    public function itemPrefixIcon(string | BackedEnum | Htmlable | Closure | null $icon): static
    {
        $this->itemPrefixIcon = $icon;

        return $this;
    }

    public function itemSuffixIcon(string | BackedEnum | Htmlable | Closure | null $icon): static
    {
        $this->itemSuffixIcon = $icon;

        return $this;
    }

    /**
     * @param  string | array<mixed> | Closure | null  $color
     */
    public function itemIconColor(string | array | Closure | null $color): static
    {
        $this->itemIconColor = $color;

        return $this;
    }

    public function hasItemLabels(): bool
    {
        return $this->itemLabel !== null
            || $this->itemDescription !== null
            || $this->itemPrefixIcon !== null
            || $this->itemSuffixIcon !== null;
    }

    public function getItemLabel(string $key, ?int $index = null): string | Htmlable | null
    {
        $container = $this->getChildSchema($key);

        return $this->resolveItemLabel([
            'container' => $container,
            'item' => $container,
            'key' => $key,
            'schema' => $container,
            'state' => $container?->getStateSnapshot() ?? [],
            'uuid' => $key,
            'index' => $index,
        ]);
    }

    /**
     * @param  array<string, mixed>  $injections
     */
    protected function resolveItemLabel(array $injections): string | Htmlable | null
    {
        $label = $this->evaluate($this->itemLabel, $injections);
        $description = $this->evaluate($this->itemDescription, $injections);
        $prefixIcon = $this->evaluate($this->itemPrefixIcon, $injections);
        $suffixIcon = $this->evaluate($this->itemSuffixIcon, $injections);
        /** @var string | array<mixed> | null $iconColor */
        $iconColor = $this->evaluate($this->itemIconColor, $injections);

        if (blank($description) && blank($prefixIcon) && blank($suffixIcon) && blank($iconColor)) {
            return $label;
        }

        $title = $this->stringFromEvaluatedLabel($label);

        $content = Content::make();

        if (filled($title)) {
            $content->title($title);
        }

        if (filled($description)) {
            $content->description((string) $description);
        }

        if (filled($prefixIcon)) {
            /** @var string | BackedEnum | Htmlable $prefixIcon */
            $content->prefixIcon($prefixIcon);
        }

        if (filled($suffixIcon)) {
            /** @var string | BackedEnum | Htmlable $suffixIcon */
            $content->suffixIcon($suffixIcon);
        }

        if (filled($iconColor)) {
            $content->iconColor($iconColor);
        }

        return new HtmlString($content->render());
    }

    protected function stringFromEvaluatedLabel(mixed $label): ?string
    {
        if ($label instanceof Htmlable) {
            $html = trim(strip_tags($label->toHtml()));

            return filled($html) ? $html : null;
        }

        if (blank($label)) {
            return null;
        }

        return (string) $label;
    }
}
