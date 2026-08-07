<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PresentedPost extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'posts';

    public function author(): BelongsTo
    {
        return $this->belongsTo(PresentedAuthor::class, 'author_id');
    }
}
