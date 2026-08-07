<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WidgetOwner extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }
}
