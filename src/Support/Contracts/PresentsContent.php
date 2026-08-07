<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Support\Contracts;

use Bensondevs\Fiddy\Support\Content;
use Illuminate\Database\Eloquent\Model;

interface PresentsContent
{
    public function __construct(Model $record);

    public function toContent(): Content;
}
