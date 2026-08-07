<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Forms\Components\FiddySelect\Contracts;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Illuminate\Database\Eloquent\Model;

interface PresentsOption
{
    public function __construct(Model $record);

    public function toOption(): Option;
}
