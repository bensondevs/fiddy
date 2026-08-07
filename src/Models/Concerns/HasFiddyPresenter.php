<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Models\Concerns;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Contracts\PresentsOption;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Support\Contracts\PresentsContent;
use Bensondevs\Fiddy\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Declare a presenter once on the model:
 *
 * ```php
 * class User extends Model implements FiddyComponentsPresentable
 * {
 *     use HasFiddyPresenter;
 *
 *     protected static string $fiddyPresenter = UserPresenter::class;
 * }
 * ```
 *
 * @phpstan-require-implements \Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable
 * @phpstan-require-extends Model
 */
trait HasFiddyPresenter
{
    /**
     * @return class-string<PresentsOption&PresentsContent>
     */
    public static function getFiddyPresenterClass(): string
    {
        if (! property_exists(static::class, 'fiddyPresenter')) {
            throw new InvalidArgumentException(
                static::class . ' must define protected static string $fiddyPresenter.',
            );
        }

        /** @var mixed $presenter */
        $presenter = static::$fiddyPresenter;

        if (! is_string($presenter) || $presenter === '') {
            throw new InvalidArgumentException(
                static::class . ' must define protected static string $fiddyPresenter.',
            );
        }

        if (
            ! is_a($presenter, PresentsOption::class, allow_string: true)
            || ! is_a($presenter, PresentsContent::class, allow_string: true)
        ) {
            throw new InvalidArgumentException(
                "[{$presenter}] must implement " . PresentsOption::class . ' and ' . PresentsContent::class . '.',
            );
        }

        return $presenter;
    }

    /**
     * @return PresentsOption&PresentsContent
     */
    protected function newFiddyPresenter(): PresentsOption
    {
        if (! $this instanceof Model) {
            throw new InvalidArgumentException(
                HasFiddyPresenter::class . ' may only be used on ' . Model::class . ' instances.',
            );
        }

        /** @var class-string<PresentsOption&PresentsContent> $presenterClass */
        $presenterClass = static::getFiddyPresenterClass();

        return new $presenterClass($this);
    }

    public function asOption(): Option
    {
        return $this->newFiddyPresenter()->toOption();
    }

    public function asColumnContent(): Content
    {
        return $this->newFiddyPresenter()->toContent();
    }

    public function asEntryContent(): Content
    {
        return $this->newFiddyPresenter()->toContent();
    }

    public function asFilterIndicator(): Indicator
    {
        $option = $this->asOption();
        $indicator = Indicator::make($option->getTitle());

        $icon = $option->getPrefixIcon();

        if (filled($icon)) {
            $indicator->prefixIcon($icon);
        }

        return $indicator;
    }
}
