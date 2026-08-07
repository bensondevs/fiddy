<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Illuminate\Database\Eloquent\Model;

final class PlainAuthor extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'authors';
}
