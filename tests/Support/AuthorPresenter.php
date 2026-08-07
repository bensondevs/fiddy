<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Support\Presenter;
use Illuminate\Database\Eloquent\Model;

/** @property Author $record */
final class AuthorPresenter extends Presenter
{
    public function toOption(): Option
    {
        /** @var Author|Model $record */
        $record = $this->record;

        return Option::make($record->getKey())
            ->title($record->name)
            ->description('Presented: ' . $record->email)
            ->image($record->avatar_url)
            ->circularImage();
    }

    public function toContent(): Content
    {
        /** @var Author|Model $record */
        $record = $this->record;

        return Content::make()
            ->title($record->name, 'name')
            ->description('Presented: ' . $record->email, 'email')
            ->image($record->avatar_url);
    }
}
