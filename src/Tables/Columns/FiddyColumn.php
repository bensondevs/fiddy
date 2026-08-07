<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tables\Columns;

use Bensondevs\Fiddy\Concerns\ResolvesPresentedContent;
use Bensondevs\Fiddy\Support\Content;
use Closure;
use Filament\Support\Services\RelationshipOrderer;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

class FiddyColumn extends TextColumn
{
    use ResolvesPresentedContent;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->html()
            ->placeholder('-')
            ->getStateUsing(fn (): ?Model => $this->getRelatedRecord())
            ->formatStateUsing(fn (): ?Htmlable => $this->getFormattedRelatedContentHtml());
    }

    /**
     * @param  bool | array<string> | string | Closure  $condition
     */
    public function searchable(
        bool | array | string | Closure $condition = true,
        ?Closure $query = null,
        bool $isIndividual = false,
        bool $isGlobal = true,
    ): static {
        parent::searchable($condition, $query, $isIndividual, $isGlobal);

        if ($query === null && (is_bool($condition) ? $condition : true) && $this->searchQuery === null) {
            $this->searchQuery = function (EloquentBuilder $query, string $search): void {
                $this->applyRelatedSearchConstraint($query, $search);
            };
        }

        return $this;
    }

    /**
     * @param  bool | array<string> | Closure  $condition
     */
    public function sortable(bool | array | Closure $condition = true, ?Closure $query = null): static
    {
        parent::sortable($condition, $query);

        if ($query === null && (is_bool($condition) ? $condition : true) && $this->sortQuery === null) {
            $this->sortQuery = function (EloquentBuilder $query, string $direction): void {
                $this->applyRelatedSort($query, $direction);
            };
        }

        return $this;
    }

    public function filterable(bool $condition = true): static
    {
        if (! $condition) {
            return $this;
        }

        return $this->searchable(condition: true, isIndividual: true);
    }

    public function applyEagerLoading(EloquentBuilder | Relation $query): EloquentBuilder | Relation
    {
        $relationshipName = $this->getFiddyRelationshipName($query->getModel());

        if (blank($relationshipName)) {
            return parent::applyEagerLoading($query);
        }

        if (array_key_exists($relationshipName, $query->getEagerLoads())) {
            return $query;
        }

        return $query->with([$relationshipName]);
    }

    protected function applyRelatedSearchConstraint(EloquentBuilder $query, string $search): void
    {
        $relationshipName = $this->getFiddyRelationshipName($query->getModel());

        if (blank($relationshipName)) {
            return;
        }

        $attributes = $this->getDefaultRelatedQueryAttributes();

        $query->whereHas(
            $relationshipName,
            function (EloquentBuilder $relatedQuery) use ($search, $attributes): void {
                $relatedQuery->where(function (EloquentBuilder $inner) use ($search, $attributes): void {
                    foreach ($attributes as $attribute) {
                        $inner->orWhere($attribute, 'like', "%{$search}%");
                    }
                });
            },
        );
    }

    protected function applyRelatedSort(EloquentBuilder $query, string $direction): void
    {
        $relationshipName = $this->getFiddyRelationshipName($query->getModel());

        if (blank($relationshipName)) {
            return;
        }

        $sortAttribute = $this->getDefaultRelatedSortAttribute();

        $query->orderBy(
            app(RelationshipOrderer::class)->buildSubquery($query, $relationshipName, $sortAttribute),
            $direction,
        );
    }

    /**
     * @return list<string>
     */
    protected function getDefaultRelatedQueryAttributes(): array
    {
        return Content::FALLBACK_SEARCH_ATTRIBUTES;
    }

    protected function getDefaultRelatedSortAttribute(): string
    {
        return Content::FALLBACK_SORT_ATTRIBUTES[0];
    }

    protected function getFiddyRelationshipName(Model $record): ?string
    {
        $name = $this->getName();

        if (blank($name) || str_contains($name, '.')) {
            return $this->getRelationshipName($record);
        }

        if (! $record->isRelation($name)) {
            return null;
        }

        $relation = $record->{$name}();

        if ($relation instanceof BelongsTo || $relation instanceof HasOne) {
            return $name;
        }

        return null;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return $this->resolveRelatedClosureDependencyForEvaluationByName($parameterName)
            ?? parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName);
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        return $this->resolveRelatedClosureDependencyForEvaluationByType($parameterType)
            ?? parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
    }
}
