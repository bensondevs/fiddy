<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Model;

final class Author extends Model implements FiddyComponentsPresentable
{
    public $timestamps = false;

    protected $guarded = [];

    public function asOption(): Option
    {
        return Option::make($this->getKey())
            ->title($this->name)
            ->description($this->email)
            ->hint($this->phone)
            ->image($this->avatar_url);
    }

    public function asColumnContent(): Content
    {
        return Content::make()
            ->title($this->name, 'name')
            ->description($this->email, 'email')
            ->image($this->avatar_url);
    }

    public function asEntryContent(): Content
    {
        return Content::make()
            ->title($this->name, 'name')
            ->description($this->email, 'email')
            ->hint($this->phone)
            ->image($this->avatar_url);
    }

    public function asFilterIndicator(): Indicator
    {
        return Indicator::make($this->name);
    }
}
