<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Illuminate\Database\Eloquent\Model;

final class Article extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
