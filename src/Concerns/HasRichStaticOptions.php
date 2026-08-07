<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Concerns;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\OptionGuesser;
use Closure;
use Illuminate\Contracts\Support\Arrayable;

trait HasRichStaticOptions
{
    /**
     * @var array<string | int, string | array<string>> | Arrayable | null
     */
    protected array | Arrayable | null $optionTitles = null;

    /**
     * @var array<string | int, string | null> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionDescriptions = null;

    /**
     * @var array<string | int, string | null> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionHints = null;

    /**
     * @var array<string | int, string | null> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionPrefixImages = null;

    /**
     * @var array<string | int, string | null> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionSuffixImages = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionPrefixIcons = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionSuffixIcons = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionTitlePrefixIcons = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionTitleSuffixIcons = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionDescriptionPrefixIcons = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionDescriptionSuffixIcons = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionHintPrefixIcons = null;

    /**
     * @var array<string | int, mixed> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $optionHintSuffixIcons = null;

    protected bool | Closure $circularImages = false;

    /**
     * When true, enum() only sets Filament's enum (no rich Option HTML).
     * Used so options(SomeEnum::class) stays plain Filament labels.
     */
    protected bool $deferRichEnumOptions = false;

    /**
     * @var array<string, bool>
     */
    protected array $presentedDisabledOptionValues = [];

    /**
     * Alias for Filament's options() — the only path that writes into Filament's options bag.
     *
     * @param  array<string | array<string>> | Arrayable | string | Closure | null  $options
     */
    public function defaultOptions(array | Arrayable | string | Closure | null $options): static
    {
        return parent::options($options);
    }

    /**
     * Static value => title map for Fiddy enrichment.
     * Closures / enum class strings / other strings pass through to Filament (plain labels).
     * Use enum() for rich HasLabel / HasIcon / presentable enum options.
     *
     * @param  array<string | array<string>> | Arrayable | string | Closure | null  $options
     */
    public function options(array | Arrayable | string | Closure | null $options): static
    {
        if ($options instanceof Closure || is_string($options) || $options === null) {
            // Filament's options(Enum::class) calls enum(); skip rich enrichment for that path.
            if (is_string($options) && enum_exists($options)) {
                $this->deferRichEnumOptions = true;

                try {
                    return $this->defaultOptions($options);
                } finally {
                    $this->deferRichEnumOptions = false;
                }
            }

            return $this->defaultOptions($options);
        }

        $this->optionTitles = $options;

        return $this->rebuildStaticOptions();
    }

    /**
     * Rich enum options: HasLabel / HasIcon / HasDescription, or asOption() when presentable.
     *
     * @param  class-string<\UnitEnum> | Closure | null  $enum
     */
    public function enum(string | Closure | null $enum): static
    {
        parent::enum($enum);

        if ($this->deferRichEnumOptions) {
            return $this;
        }

        if (is_string($enum) && enum_exists($enum)) {
            return $this->optionsFromEnum($enum);
        }

        return $this;
    }

    /**
     * @param  class-string<\UnitEnum>  $enum
     */
    protected function optionsFromEnum(string $enum): static
    {
        return $this->defaultOptions(function () use ($enum): array {
            $this->presentedDisabledOptionValues = [];

            $circular = (bool) $this->evaluate($this->circularImages);
            $htmlOptions = [];

            foreach ($enum::cases() as $case) {
                $option = OptionGuesser::from($case);

                if (! $option instanceof Option) {
                    continue;
                }

                if ($circular) {
                    $option->circularImage();
                }

                $value = $option->getValue() ?? OptionGuesser::enumValue($case);

                $this->rememberPresentedOptionDisabled($value, $option);

                $htmlOptions[$value] = $option->render();
            }

            return $htmlOptions;
        });
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $descriptions
     */
    public function descriptions(array | Arrayable | Closure | null $descriptions): static
    {
        $this->optionDescriptions = $descriptions;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $hints
     */
    public function hints(array | Arrayable | Closure | null $hints): static
    {
        $this->optionHints = $hints;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $images
     */
    public function images(array | Arrayable | Closure | null $images): static
    {
        return $this->prefixImages($images);
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $images
     */
    public function prefixImages(array | Arrayable | Closure | null $images): static
    {
        $this->optionPrefixImages = $images;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $images
     */
    public function suffixImages(array | Arrayable | Closure | null $images): static
    {
        $this->optionSuffixImages = $images;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function icons(array | Arrayable | Closure | null $icons): static
    {
        return $this->prefixIcons($icons);
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function prefixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionPrefixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function suffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionSuffixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function titleIcons(array | Arrayable | Closure | null $icons): static
    {
        return $this->titlePrefixIcons($icons);
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function titlePrefixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionTitlePrefixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function titleSuffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionTitleSuffixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function descriptionIcons(array | Arrayable | Closure | null $icons): static
    {
        return $this->descriptionPrefixIcons($icons);
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function descriptionPrefixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionDescriptionPrefixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function descriptionSuffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionDescriptionSuffixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function hintIcons(array | Arrayable | Closure | null $icons): static
    {
        return $this->hintPrefixIcons($icons);
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function hintPrefixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionHintPrefixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function hintSuffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionHintSuffixIcons = $icons;

        return $this->rebuildStaticOptions();
    }

    public function circularImages(bool | Closure $condition = true): static
    {
        $this->circularImages = $condition;

        return $this->rebuildStaticOptions();
    }

    protected function wirePresentedOptionDisabled(): void
    {
        $this->disableOptionWhen(
            fn (mixed $value): bool => $this->isPresentedOptionDisabled($value),
            merge: true,
        );
    }

    protected function isPresentedOptionDisabled(mixed $value): bool
    {
        return $this->presentedDisabledOptionValues[(string) $value] ?? false;
    }

    protected function rememberPresentedOptionDisabled(string | int | null $value, Option $option): void
    {
        if ($value === null) {
            return;
        }

        $key = (string) $value;

        if ($option->isDisabled()) {
            $this->presentedDisabledOptionValues[$key] = true;

            return;
        }

        unset($this->presentedDisabledOptionValues[$key]);
    }

    protected function rebuildStaticOptions(): static
    {
        if (blank($this->optionTitles)) {
            return $this;
        }

        $titles = $this->optionTitles instanceof Arrayable
            ? $this->optionTitles->toArray()
            : $this->optionTitles;

        if (blank($titles)) {
            $this->presentedDisabledOptionValues = [];

            return $this->defaultOptions([]);
        }

        return $this->defaultOptions(function () use ($titles): array {
            $this->presentedDisabledOptionValues = [];

            $descriptions = $this->normalizeMap($this->optionDescriptions);
            $hints = $this->normalizeMap($this->optionHints);
            $prefixImages = $this->normalizeMap($this->optionPrefixImages);
            $suffixImages = $this->normalizeMap($this->optionSuffixImages);
            $prefixIcons = $this->normalizeMap($this->optionPrefixIcons);
            $suffixIcons = $this->normalizeMap($this->optionSuffixIcons);
            $titlePrefixIcons = $this->normalizeMap($this->optionTitlePrefixIcons);
            $titleSuffixIcons = $this->normalizeMap($this->optionTitleSuffixIcons);
            $descriptionPrefixIcons = $this->normalizeMap($this->optionDescriptionPrefixIcons);
            $descriptionSuffixIcons = $this->normalizeMap($this->optionDescriptionSuffixIcons);
            $hintPrefixIcons = $this->normalizeMap($this->optionHintPrefixIcons);
            $hintSuffixIcons = $this->normalizeMap($this->optionHintSuffixIcons);
            $circular = (bool) $this->evaluate($this->circularImages);

            $htmlOptions = [];

            foreach ($titles as $value => $title) {
                if (is_array($title)) {
                    if (! OptionGuesser::looksLikeOptionAttributes($title)) {
                        $htmlOptions[$value] = $title;

                        continue;
                    }

                    $option = OptionGuesser::from($title, $value) ?? Option::make($value);
                } else {
                    $option = Option::make($value)
                        ->title(is_string($title) ? $title : (string) $title)
                        ->description($descriptions[$value] ?? null)
                        ->hint($hints[$value] ?? null)
                        ->prefixImage($prefixImages[$value] ?? null)
                        ->suffixImage($suffixImages[$value] ?? null)
                        ->prefixIcon($prefixIcons[$value] ?? null)
                        ->suffixIcon($suffixIcons[$value] ?? null)
                        ->titlePrefixIcon($titlePrefixIcons[$value] ?? null)
                        ->titleSuffixIcon($titleSuffixIcons[$value] ?? null)
                        ->descriptionPrefixIcon($descriptionPrefixIcons[$value] ?? null)
                        ->descriptionSuffixIcon($descriptionSuffixIcons[$value] ?? null)
                        ->hintPrefixIcon($hintPrefixIcons[$value] ?? null)
                        ->hintSuffixIcon($hintSuffixIcons[$value] ?? null);
                }

                if ($circular) {
                    $option->circularImage();
                }

                $this->rememberPresentedOptionDisabled($value, $option);

                $htmlOptions[$value] = $option->render();
            }

            return $htmlOptions;
        });
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $map
     * @return array<string | int, mixed>
     */
    protected function normalizeMap(array | Arrayable | Closure | null $map): array
    {
        if (blank($map)) {
            return [];
        }

        $resolved = $this->evaluate($map);

        if ($resolved instanceof Arrayable) {
            return $resolved->toArray();
        }

        return is_array($resolved) ? $resolved : [];
    }
}
