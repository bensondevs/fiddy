<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

final class ResolvesActivitySubject
{
    public static function forTimestampColumn(Model $record, string $columnName): ?Model
    {
        return match ($columnName) {
            'created_at' => self::created($record),
            'updated_at' => self::updated($record),
            default => null,
        };
    }

    public static function created(Model $record): ?Model
    {
        $causer = self::causerFromAuthorshipRelation($record, 'latestCreatedActivity')
            ?? self::causerFromActivities($record, 'created');

        if ($causer !== null) {
            return $causer;
        }

        if (! method_exists($record, 'createdBy')) {
            return null;
        }

        $record->loadMissing('createdBy');

        $createdBy = $record->getRelationValue('createdBy');

        return $createdBy instanceof Model ? $createdBy : null;
    }

    public static function updated(Model $record): ?Model
    {
        return self::causerFromAuthorshipRelation($record, 'latestUpdatedActivity')
            ?? self::causerFromActivities($record, 'updated')
            ?? self::created($record);
    }

    public static function name(?Model $subject): ?string
    {
        if (blank($subject)) {
            return null;
        }

        $name = $subject->getAttribute('name');

        return filled($name) ? (string) $name : null;
    }

    private static function causerFromAuthorshipRelation(Model $record, string $relation): ?Model
    {
        if (! method_exists($record, $relation)) {
            return null;
        }

        if (! $record->relationLoaded($relation)) {
            return null;
        }

        $activity = $record->getRelation($relation);

        if (! $activity instanceof Model) {
            return null;
        }

        if (! $activity->relationLoaded('causer')) {
            $activity->load('causer');
        }

        $causer = $activity->getRelationValue('causer');

        return $causer instanceof Model ? $causer : null;
    }

    private static function causerFromActivities(Model $record, string $event): ?Model
    {
        if (! method_exists($record, 'activitiesAsSubject')) {
            return null;
        }

        if ($record->relationLoaded('activitiesAsSubject')) {
            /** @var Collection<int, Model> $activities */
            $activities = $record->getRelation('activitiesAsSubject');

            $activity = $event === 'created'
                ? $activities
                    ->filter(fn (Model $activity): bool => self::activityEventIs($activity, $event))
                    ->sortBy(fn (Model $activity): int | string => $activity->getKey())
                    ->first()
                : $activities
                    ->filter(fn (Model $activity): bool => self::activityEventIs($activity, $event))
                    ->sortByDesc(fn (Model $activity): int | string => $activity->getKey())
                    ->first();

            if (! $activity instanceof Model) {
                return null;
            }

            if (! $activity->relationLoaded('causer')) {
                $activity->load('causer');
            }

            $causer = $activity->getRelationValue('causer');

            return $causer instanceof Model ? $causer : null;
        }

        /** @var Relation $relation */
        $relation = $record->activitiesAsSubject();

        $query = $relation
            ->where('event', $event)
            ->with('causer');

        $activity = $event === 'created'
            ? $query->oldest()->first()
            : $query->latest()->first();

        $causer = $activity?->causer;

        return $causer instanceof Model ? $causer : null;
    }

    private static function activityEventIs(Model $activity, string $event): bool
    {
        $value = $activity->getAttribute('event');

        if (is_object($value) && property_exists($value, 'value')) {
            return (string) $value->value === $event;
        }

        return (string) $value === $event;
    }
}
