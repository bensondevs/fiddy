<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Concerns;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Contracts\PresentsOption;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\OptionGuesser;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Closure;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

trait ResolvesPresentedOption
{
    protected ?Closure $presentOptionUsing = null;

    /**
     * @var class-string<PresentsOption>|null
     */
    protected ?string $optionPresenterClass = null;

    public function presentOptionUsing(?Closure $callback): static
    {
        $this->presentOptionUsing = $callback;

        $this->afterConfiguringPresentation();

        return $this;
    }

    /**
     * @param  class-string<PresentsOption>|null  $presenter
     */
    public function presentUsing(?string $presenter): static
    {
        if (filled($presenter) && ! is_a($presenter, PresentsOption::class, allow_string: true)) {
            throw new InvalidArgumentException(
                "[{$presenter}] must implement " . PresentsOption::class . '.',
            );
        }

        $this->optionPresenterClass = $presenter;

        $this->afterConfiguringPresentation();

        return $this;
    }

    protected function afterConfiguringPresentation(): void
    {
        //
    }

    protected function resolvePresentedOption(Model $record): ?Option
    {
        if (filled($this->presentOptionUsing)) {
            /** @var mixed $option */
            $option = $this->evaluate(
                $this->presentOptionUsing,
                namedInjections: [
                    'record' => $record,
                    'related' => $record,
                ],
                typedInjections: [
                    Model::class => $record,
                    $record::class => $record,
                ],
            );

            return $option instanceof Option ? $option : null;
        }

        if (filled($this->optionPresenterClass)) {
            /** @var class-string<PresentsOption> $presenterClass */
            $presenterClass = $this->optionPresenterClass;
            $presenter = new $presenterClass($record);

            return $presenter->toOption();
        }

        if ($record instanceof FiddyComponentsPresentable) {
            return $record->asOption();
        }

        return OptionGuesser::from($record);
    }
}
