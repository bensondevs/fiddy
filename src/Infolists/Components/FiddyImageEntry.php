<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Infolists\Components;

use Bensondevs\Fiddy\Concerns\HasMaxImageDimensions;
use Bensondevs\Fiddy\Support\ImageDimensions;
use Closure;
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\TextSize;
use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;

use function Filament\Support\generate_href_html;

class FiddyImageEntry extends ImageEntry
{
    use HasMaxImageDimensions;

    protected bool | Closure $isRounded = false;

    protected bool | Closure $isPreviewable = true;

    public function rounded(bool | Closure $condition = true): static
    {
        $this->isRounded = $condition;

        return $this;
    }

    public function isRounded(): bool
    {
        return (bool) $this->evaluate($this->isRounded);
    }

    public function previewable(bool | Closure $condition = true): static
    {
        $this->isPreviewable = $condition;

        return $this;
    }

    public function isPreviewable(): bool
    {
        return (bool) $this->evaluate($this->isPreviewable);
    }

    protected function defaultMaxImageWidth(): ?string
    {
        return $this->isStacked() ? '2.5rem' : '8rem';
    }

    protected function defaultMaxImageHeight(): ?string
    {
        return $this->isStacked() ? '2.5rem' : '8rem';
    }

    /**
     * @return array<string, string>
     */
    protected function resolveImageDimensionStyles(): array
    {
        $height = $this->getImageHeight();
        $width = $this->getImageWidth();
        $maxWidth = $this->getMaxImageWidth();
        $maxHeight = $this->getMaxImageHeight();
        $isCircular = $this->isCircular();
        $isSquare = $this->isSquare();

        if (($isCircular || $isSquare) && blank($width) && filled($height)) {
            $width = $height;
        }

        if (($isCircular || $isSquare) && blank($width) && blank($height)) {
            $size = $maxWidth ?? $maxHeight;
            $width = $size;
            $height = $size;
        }

        return ImageDimensions::styles($width, $height, $maxWidth, $maxHeight);
    }

    public function toEmbeddedHtml(): string
    {
        $state = $this->getState();

        if ($state instanceof Collection) {
            $state = $state->all();
        }

        $attributes = $this->getExtraAttributeBag()
            ->class([
                'fi-in-image',
            ]);

        $defaultImageUrl = $this->getDefaultImageUrl();

        if (blank($state) && filled($defaultImageUrl)) {
            $state = [null];
        }

        if (blank($state)) {
            $attributes = $attributes
                ->merge([
                    'x-tooltip' => filled($tooltip = $this->getEmptyTooltip())
                        ? '{
                            content: ' . Js::from($tooltip) . ',
                            theme: $store.theme,
                            allowHTML: ' . Js::from($tooltip instanceof Htmlable) . ',

                        }'
                        : null,
                ], escape: false);

            $placeholder = $this->getPlaceholder();

            ob_start(); ?>

            <div <?= $attributes->toHtml() ?>>
                <?php if (filled($placeholder)) { ?>
                    <p class="fi-in-placeholder">
                        <?= e($placeholder) ?>
                    </p>
                <?php } ?>
            </div>

            <?php return $this->wrapEmbeddedHtml(ob_get_clean());
        }

        $state = Arr::wrap($state);
        $stateCount = count($state);

        $limit = $this->getLimit() ?? $stateCount;

        $stateOverLimitCount = ($limit && ($stateCount > $limit))
            ? ($stateCount - $limit)
            : 0;

        if ($stateOverLimitCount) {
            $state = array_slice($state, 0, $limit);
        }

        $alignment = $this->getAlignment();
        $isCircular = $this->isCircular();
        $isStacked = $this->isStacked();
        $isRounded = $this->isRounded() && ! $isCircular;
        $entryHasNonStateUrl = filled($this->getUrl());
        $isPreviewable = $this->isPreviewable() && ! $entryHasNonStateUrl;
        $hasLimitedRemainingText = $stateOverLimitCount && $this->hasLimitedRemainingText();
        $limitedRemainingTextSize = $this->getLimitedRemainingTextSize();
        $dimensionStyles = $this->resolveImageDimensionStyles();

        $attributes = $attributes
            ->class([
                'fi-circular' => $isCircular,
                'fi-rounded' => $isRounded,
                'fi-previewable' => $isPreviewable,
                'fi-wrapped' => $this->canWrap(),
                'fi-stacked' => $isStacked,
                ($isStacked && is_int($ring = $this->getRing())) ? "fi-in-image-ring fi-in-image-ring-{$ring}" : '',
                ($isStacked && ($overlap = ($this->getOverlap() ?? 2))) ? "fi-in-image-overlap-{$overlap}" : '',
                ($alignment instanceof Alignment) ? "fi-align-{$alignment->value}" : (is_string($alignment) ? $alignment : ''),
            ]);

        if ($isPreviewable) {
            $attributes = $attributes->merge([
                'x-data' => '{ previewUrl: null }',
            ], escape: false);
        }

        $shouldOpenUrlInNewTab = $this->shouldOpenUrlInNewTab();

        $formatState = function (mixed $stateItem) use ($defaultImageUrl, $dimensionStyles, $shouldOpenUrlInNewTab, $isPreviewable): string {
            $imageSrc = filled($stateItem)
                ? ($this->getImageUrl($stateItem) ?? $defaultImageUrl)
                : $defaultImageUrl;

            $item = '<img ' . $this->getExtraImgAttributeBag()
                ->merge([
                    'alt' => e($this->getAlt($stateItem) ?? ''),
                    'src' => e($imageSrc),
                    'x-tooltip' => filled($tooltip = $this->getTooltip($stateItem))
                        ? '{
                                content: ' . Js::from($tooltip) . ',
                                theme: $store.theme,
                                allowHTML: ' . Js::from($tooltip instanceof Htmlable) . ',
                            }'
                        : null,
                ], escape: false)
                ->style(collect($dimensionStyles)
                    ->mapWithKeys(fn (string $value, string $property): array => [
                        ("{$property}: " . e($value)) => true,
                    ])
                    ->all())
                ->toHtml()
                . ' />';

            if (filled($url = $this->getUrl($stateItem))) {
                return '<a ' . generate_href_html($url, $shouldOpenUrlInNewTab)->toHtml() . '>' . $item . '</a>';
            }

            if ($isPreviewable && filled($imageSrc)) {
                return '<button type="button" class="fi-in-image-preview-trigger" @click="previewUrl = ' . Js::from($imageSrc) . '">' . $item . '</button>';
            }

            return $item;
        };

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?php foreach ($state as $stateItem) { ?>
                <?= $formatState($stateItem) ?>
            <?php } ?>

            <?php if ($hasLimitedRemainingText) { ?>
                <div <?= (new FilamentComponentAttributeBag)
                ->class([
                    'fi-in-image-limited-remaining-text',
                    (($limitedRemainingTextSize instanceof TextSize) ? "fi-size-{$limitedRemainingTextSize->value}" : $limitedRemainingTextSize) => $limitedRemainingTextSize,
                ])
                ->style(collect($dimensionStyles)
                    ->mapWithKeys(fn (string $value, string $property): array => [
                        ("{$property}: " . e($value)) => true,
                    ])
                    ->all())
                ->toHtml() ?>>
                    +<?= $stateOverLimitCount ?>
                </div>
            <?php } ?>

            <?php if ($isPreviewable) { ?>
                <div
                    x-show="previewUrl"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click.self="previewUrl = null"
                    @keydown.escape.window="previewUrl = null"
                    class="fi-in-image-preview-overlay"
                    style="display: none;"
                >
                    <img
                        :src="previewUrl"
                        alt=""
                        @click.stop
                        class="fi-in-image-preview"
                    />
                </div>
            <?php } ?>
        </div>

        <?php return $this->wrapEmbeddedHtml(ob_get_clean());
    }
}
