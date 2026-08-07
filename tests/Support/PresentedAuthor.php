<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Bensondevs\Fiddy\Models\Concerns\HasFiddyPresenter;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Illuminate\Database\Eloquent\Model;

final class PresentedAuthor extends Model implements FiddyComponentsPresentable
{
    use HasFiddyPresenter;

    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'authors';

    protected static string $fiddyPresenter = AuthorPresenter::class;
}
