<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Forms\Components;

use Bensondevs\Fiddy\Concerns\HasRichStaticOptions;
use Bensondevs\Fiddy\Concerns\ResolvesPresentedOption;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Closure;
use Filament\Forms\Components\Select as FilamentSelect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FiddySelect extends FilamentSelect
{
    use HasRichStaticOptions;
    use ResolvesPresentedOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->allowHtml()
            ->native(false)
            ->extraAttributes(fn (): array => [
                'class' => 'fiddy-select',
            ], merge: true);

        $this->wirePresentedOptionDisabled();
    }

    public function relationship(
        string | Closure | null $name = null,
        string | Closure | null $titleAttribute = null,
        ?Closure $modifyQueryUsing = null,
        bool $ignoreRecord = false,
    ): static {
        parent::relationship($name, $titleAttribute, $modifyQueryUsing, $ignoreRecord);

        $this->wireRecordPresentation();

        return $this;
    }

    protected function afterConfiguringPresentation(): void
    {
        $this->wireRecordPresentation();
    }

    protected function wireRecordPresentation(): void
    {
        if (! $this->hasRelationship()) {
            return;
        }

        $this->getOptionLabelFromRecordUsing(
            fn (Model $record): string => $this->resolveOptionLabelFromRecord($record),
        );
    }

    protected function resolveOptionLabelFromRecord(Model $record): string
    {
        $option = $this->resolveOptionFromRecord($record);

        if ($option instanceof Option) {
            $this->rememberPresentedOptionDisabled(
                $option->getValue() ?? $record->getKey(),
                $option,
            );

            return $this->applySelectDefaultsToOption($option)->render();
        }

        $relationshipTitleAttribute = $this->getRelationshipTitleAttribute();

        if (filled($relationshipTitleAttribute) && str_contains($relationshipTitleAttribute, '->')) {
            $relationshipTitleAttribute = str_replace('->', '.', $relationshipTitleAttribute);
        }

        return (string) data_get($record, $relationshipTitleAttribute);
    }

    protected function resolveOptionFromRecord(Model $record): ?Option
    {
        $option = $this->resolvePresentedOption($record);

        if (! $option instanceof Option) {
            return null;
        }

        if (blank($option->getTitle())) {
            $relationshipTitleAttribute = $this->getRelationshipTitleAttribute();

            if (filled($relationshipTitleAttribute) && str_contains($relationshipTitleAttribute, '->')) {
                $relationshipTitleAttribute = str_replace('->', '.', $relationshipTitleAttribute);
            }

            if (filled($relationshipTitleAttribute)) {
                $title = data_get($record, $relationshipTitleAttribute);

                if (filled($title)) {
                    $option->title(is_string($title) ? $title : (string) $title);
                }
            }
        }

        return $option;
    }

    protected function applySelectDefaultsToOption(Option $option): Option
    {
        if ($this->evaluate($this->circularImages)) {
            $option->circularImage();
        }

        return $option;
    }

    protected function isPresentedOptionDisabled(mixed $value): bool
    {
        if ($this->presentedDisabledOptionValues[(string) $value] ?? false) {
            return true;
        }

        if (! $this->hasRelationship()) {
            return false;
        }

        $record = $this->resolveRelationshipRecordForOptionValue($value);

        if (! $record instanceof Model) {
            return false;
        }

        $option = $this->resolveOptionFromRecord($record);

        return $option?->isDisabled() ?? false;
    }

    protected function resolveRelationshipRecordForOptionValue(mixed $value): ?Model
    {
        /** @var Collection<int, Model>|null $cachedRecords */
        $cachedRecords = $this->cachedRelationshipRecords;

        if ($cachedRecords instanceof Collection) {
            $record = $cachedRecords->first(
                fn (Model $record): bool => (string) $record->getKey() === (string) $value,
            );

            if ($record instanceof Model) {
                return $record;
            }
        }

        $relationship = $this->getRelationship();

        if (! $relationship) {
            return null;
        }

        /** @var Model|null $record */
        $record = $relationship->getRelated()->newQuery()->find($value);

        return $record;
    }
}
