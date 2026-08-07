<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class MediaAuthor extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'authors';

    public function getFirstMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        return 'https://cdn.example.com/avatar.jpg';
    }
}
