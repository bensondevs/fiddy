<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait ResolvesRelatedRecord
{
    protected Model | Closure | null $relatedRecord = null;

    protected bool | Closure $circularImages = false;

    public function relatedRecord(Model | Closure | null $record): static
    {
        $this->relatedRecord = $record;

        return $this;
    }

    public function circularImages(bool | Closure $condition = true): static
    {
        $this->circularImages = $condition;

        return $this;
    }

    public function getRelatedRecord(): ?Model
    {
        if ($this->relatedRecord instanceof Model) {
            return $this->relatedRecord;
        }

        if ($this->relatedRecord instanceof Closure) {
            $resolved = $this->evaluate($this->relatedRecord);

            return $resolved instanceof Model ? $resolved : null;
        }

        $record = $this->getRecord();

        if (! $record instanceof Model) {
            return null;
        }

        $name = $this->getName();

        if (blank($name)) {
            return null;
        }

        if (! str_contains($name, '.') && $record->isRelation($name)) {
            $relation = $record->{$name}();

            if ($relation instanceof BelongsTo || $relation instanceof HasOne) {
                $related = $relation->getResults();

                return $related instanceof Model ? $related : null;
            }
        }

        $related = data_get($record, $name);

        return $related instanceof Model ? $related : null;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveRelatedClosureDependencyForEvaluationByName(string $parameterName): ?array
    {
        return match ($parameterName) {
            'related' => [$this->getRelatedRecord()],
            default => null,
        };
    }

    /**
     * @return array<mixed>
     */
    protected function resolveRelatedClosureDependencyForEvaluationByType(string $parameterType): ?array
    {
        $related = $this->getRelatedRecord();

        if (
            $related instanceof Model
            && $parameterType !== Model::class
            && is_a($related, $parameterType)
        ) {
            return [$related];
        }

        return null;
    }
}
