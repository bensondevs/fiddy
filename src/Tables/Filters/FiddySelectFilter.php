<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tables\Filters;

use Bensondevs\Fiddy\Concerns\ResolvesPresentedOption;
use Bensondevs\Fiddy\Forms\Components\FiddySelect;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\OptionGuesser;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Indicator as FilamentIndicator;
use Filament\Tables\Filters\SelectFilter as FilamentSelectFilter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use UnitEnum;
use Znck\Eloquent\Relations\BelongsToThrough;

class FiddySelectFilter extends FilamentSelectFilter
{
    use ResolvesPresentedOption;

    /**
     * @var array<string | int, string | array<string>> | Arrayable | null
     */
    protected array | Arrayable | null $staticOptionTitles = null;

    /**
     * @var class-string<UnitEnum>|null
     */
    protected ?string $enumOptionsClass = null;

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->native(false);

        $this->indicateUsing(function (FiddySelectFilter $filter, array $state): array {
            return $filter->resolveFilterIndicators($state);
        });
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<FilamentIndicator>
     */
    protected function resolveFilterIndicators(array $state): array
    {
        if ($this->isMultiple()) {
            if (blank($state['values'] ?? null)) {
                return [];
            }

            $labels = $this->queriesRelationships()
                ? $this->resolveRelationshipFilterIndicatorLabels($state['values'])
                : $this->resolveStaticFilterIndicatorLabels($state['values']);

            if (! count($labels)) {
                return [];
            }

            $labels = collect($labels)->join(', ', ' & ');

            return [$this->makeFilterIndicatorChip($labels)];
        }

        if (blank($state['value'] ?? null)) {
            return [];
        }

        $label = $this->queriesRelationships()
            ? $this->resolveRelationshipFilterIndicatorLabel($state['value'])
            : $this->resolveStaticFilterIndicatorLabel($state['value']);

        if (blank($label)) {
            return [];
        }

        return [$this->makeFilterIndicatorChip($label)];
    }

    /**
     * @param  array<int, mixed>  $values
     * @return list<string|Htmlable>
     */
    protected function resolveRelationshipFilterIndicatorLabels(array $values): array
    {
        $labels = [];

        if (
            $this->hasEmptyRelationshipOption() &&
            in_array(self::EMPTY_RELATIONSHIP_OPTION_KEY, $values)
        ) {
            $labels[] = $this->getEmptyRelationshipOptionLabel();
        }

        $relationshipQuery = $this->getRelationshipQuery();

        $records = $relationshipQuery
            ->when(
                $this->getRelationship() instanceof BelongsToThrough,
                fn (Builder $query) => $query->distinct(),
            )
            ->when(
                $this->getRelationshipKey(),
                fn (Builder $query, string $relationshipKey) => $query->whereIn(
                    $relationshipKey,
                    $this->getRelationshipQueryValues($values),
                ),
                fn (Builder $query) => $query->whereKey($values),
            )
            ->get();

        foreach ($records as $record) {
            $label = $this->labelFromRelatedRecord($record);

            if (filled($label)) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    protected function resolveRelationshipFilterIndicatorLabel(mixed $value): string | Htmlable | null
    {
        if (
            $this->hasEmptyRelationshipOption() &&
            ($value === self::EMPTY_RELATIONSHIP_OPTION_KEY)
        ) {
            return $this->getEmptyRelationshipOptionLabel();
        }

        $record = $this->getRelationshipQuery()
            ->when(
                $this->getRelationshipKey(),
                fn (Builder $query, string $relationshipKey) => $query->where($relationshipKey, $value),
                fn (Builder $query) => $query->whereKey($value),
            )
            ->first();

        return $this->labelFromRelatedRecord($record);
    }

    protected function labelFromRelatedRecord(?Model $record): string | Htmlable | null
    {
        if (! $record instanceof Model) {
            return null;
        }

        if ($record instanceof FiddyComponentsPresentable) {
            return new HtmlString($record->asFilterIndicator()->render());
        }

        $title = $record->getAttributeValue($this->getRelationshipTitleAttribute());

        return filled($title) ? (is_string($title) ? $title : (string) $title) : null;
    }

    /**
     * @param  array<int, mixed>  $values
     * @return list<string|Htmlable>
     */
    protected function resolveStaticFilterIndicatorLabels(array $values): array
    {
        if (filled($this->enumOptionsClass)) {
            $labels = [];

            foreach ($values as $value) {
                $label = $this->labelFromEnumOption($value);

                if (filled($label)) {
                    $labels[] = $label;
                }
            }

            return $labels;
        }

        return collect($this->getOptions())
            ->mapWithKeys(fn (string | array $label, string $value): array => is_array($label) ? $label : [$value => $label])
            ->only($values)
            ->values()
            ->all();
    }

    protected function resolveStaticFilterIndicatorLabel(mixed $value): string | Htmlable | null
    {
        if (filled($this->enumOptionsClass)) {
            return $this->labelFromEnumOption($value);
        }

        return collect($this->getOptions())
            ->mapWithKeys(fn (string | array $label, string $value): array => is_array($label) ? $label : [$value => $label])
            ->get($value);
    }

    protected function labelFromEnumOption(mixed $value): string | Htmlable | null
    {
        if (! filled($this->enumOptionsClass)) {
            return null;
        }

        $case = OptionGuesser::enumCase($this->enumOptionsClass, $value);

        if (! $case instanceof UnitEnum) {
            return null;
        }

        return new HtmlString(Indicator::fromEnum($case)->render());
    }

    protected function makeFilterIndicatorChip(string | Htmlable $label): FilamentIndicator
    {
        $indicator = $this->getIndicator();

        if ($indicator instanceof FilamentIndicator) {
            return $indicator;
        }

        $prefix = is_string($indicator) || $indicator instanceof Htmlable
            ? $indicator
            : (string) $indicator;

        if ($label instanceof Htmlable) {
            return FilamentIndicator::make(new HtmlString(
                e((string) $prefix) . ': ' . $label->toHtml(),
            ));
        }

        return FilamentIndicator::make("{$prefix}: {$label}");
    }

    /**
     * @param  array<string | array<string>> | Arrayable | string | Closure | null  $options
     */
    public function options(array | Arrayable | string | Closure | null $options): static
    {
        if ($options instanceof Closure || is_string($options) || $options === null) {
            $this->staticOptionTitles = null;
            $this->enumOptionsClass = null;

            return parent::options($options);
        }

        $this->enumOptionsClass = null;
        $this->staticOptionTitles = $options;

        return parent::options($options);
    }

    /**
     * Rich enum options on the filter form field and contract-based filter chips.
     *
     * @param  class-string<UnitEnum>  $enum
     */
    public function enum(string $enum): static
    {
        $this->staticOptionTitles = null;
        $this->enumOptionsClass = $enum;

        return $this;
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $descriptions
     */
    public function descriptions(array | Arrayable | Closure | null $descriptions): static
    {
        $this->optionDescriptions = $descriptions;

        return $this;
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $hints
     */
    public function hints(array | Arrayable | Closure | null $hints): static
    {
        $this->optionHints = $hints;

        return $this;
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

        return $this;
    }

    /**
     * @param  array<string | int, string | null> | Arrayable | Closure | null  $images
     */
    public function suffixImages(array | Arrayable | Closure | null $images): static
    {
        $this->optionSuffixImages = $images;

        return $this;
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

        return $this;
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function suffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionSuffixIcons = $icons;

        return $this;
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

        return $this;
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function titleSuffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionTitleSuffixIcons = $icons;

        return $this;
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

        return $this;
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function descriptionSuffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionDescriptionSuffixIcons = $icons;

        return $this;
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

        return $this;
    }

    /**
     * @param  array<string | int, mixed> | Arrayable | Closure | null  $icons
     */
    public function hintSuffixIcons(array | Arrayable | Closure | null $icons): static
    {
        $this->optionHintSuffixIcons = $icons;

        return $this;
    }

    public function circularImages(bool | Closure $condition = true): static
    {
        $this->circularImages = $condition;

        return $this;
    }

    public function getFormField(): Select
    {
        $field = FiddySelect::make($this->isMultiple() ? 'values' : 'value')
            ->label($this->getLabel())
            ->multiple($this->isMultiple())
            ->placeholder($this->getPlaceholder())
            ->searchable($this->getSearchable())
            ->selectablePlaceholder($this->canSelectPlaceholder())
            ->preload($this->isPreloaded())
            ->native($this->isNative())
            ->optionsLimit($this->getOptionsLimit());

        $this->applyPresentationToField($field);

        if ($this->queriesRelationships()) {
            $field
                ->relationship(
                    $this->getRelationshipName(),
                    $this->getRelationshipTitleAttribute(),
                    $this->modifyRelationshipQueryUsing,
                )
                ->getSearchResultsUsing(fn (Select $component, ?string $search): array => $this->getSearchResultsFromRelationship($component, $search))
                ->options(fn (Select $component): ?array => $this->getOptionsFromRelationship($component))
                ->getOptionLabelUsing(fn (Select $component) => $this->getOptionLabelFromRelationship($component))
                ->getOptionLabelsUsing(fn (Select $component, array $values): array => $this->getOptionLabelsFromRelationship($component, $values))
                ->forceSearchCaseInsensitive($this->isSearchForcedCaseInsensitive());

            $this->applyPresentationToField($field);
        } elseif (filled($this->enumOptionsClass)) {
            $field->enum($this->enumOptionsClass);

            $this->applyStaticOptionMapsToField($field);
        } elseif (filled($this->staticOptionTitles)) {
            $field->options($this->staticOptionTitles);

            $this->applyStaticOptionMapsToField($field);
        } else {
            $field->options(fn (): array => $this->getOptions());
        }

        if ($this->getOptionLabelUsing) {
            $field->getOptionLabelUsing($this->getOptionLabelUsing);
        }

        if ($this->getOptionLabelsUsing) {
            $field->getOptionLabelsUsing($this->getOptionLabelsUsing);
        }

        if ($this->getOptionLabelFromRecordUsing) {
            $field->getOptionLabelFromRecordUsing($this->getOptionLabelFromRecordUsing);
        }

        if ($this->getSearchResultsUsing) {
            $field->getSearchResultsUsing($this->getSearchResultsUsing);
        }

        if (filled($defaultState = $this->getDefaultState())) {
            $field->default($defaultState);
        }

        return $field;
    }

    protected function applyStaticOptionMapsToField(FiddySelect $field): void
    {
        $maps = [
            'descriptions' => $this->optionDescriptions,
            'hints' => $this->optionHints,
            'prefixImages' => $this->optionPrefixImages,
            'suffixImages' => $this->optionSuffixImages,
            'prefixIcons' => $this->optionPrefixIcons,
            'suffixIcons' => $this->optionSuffixIcons,
            'titlePrefixIcons' => $this->optionTitlePrefixIcons,
            'titleSuffixIcons' => $this->optionTitleSuffixIcons,
            'descriptionPrefixIcons' => $this->optionDescriptionPrefixIcons,
            'descriptionSuffixIcons' => $this->optionDescriptionSuffixIcons,
            'hintPrefixIcons' => $this->optionHintPrefixIcons,
            'hintSuffixIcons' => $this->optionHintSuffixIcons,
        ];

        foreach ($maps as $method => $value) {
            if (filled($value)) {
                $field->{$method}($value);
            }
        }
    }

    protected function applyPresentationToField(FiddySelect $field): void
    {
        if (filled($this->presentOptionUsing)) {
            $field->presentOptionUsing($this->presentOptionUsing);
        }

        if (filled($this->optionPresenterClass)) {
            $field->presentUsing($this->optionPresenterClass);
        }

        $field
            ->circularImages($this->circularImages);
    }
}
