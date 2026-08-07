<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Support;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Contracts\PresentsOption;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Support\Contracts\PresentsContent;
use Illuminate\Database\Eloquent\Model;

abstract class Presenter implements PresentsContent, PresentsOption
{
    public function __construct(protected Model $record) {}

    abstract public function toOption(): Option;

    abstract public function toContent(): Content;

    public function render(): string
    {
        return $this->toContent()->render();
    }
}
