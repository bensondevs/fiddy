<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Support\Content;

it('defaults content images to max width and height of 2rem', function (): void {
    $html = Content::make()
        ->title('Alice')
        ->image('https://example.com/alice.jpg')
        ->render();

    expect($html)->toContain('max-width: 2rem')
        ->and($html)->toContain('max-height: 2rem')
        ->and($html)->not->toContain('h-8 w-8');
});

it('applies explicit content image dimensions', function (): void {
    $html = Content::make()
        ->title('Alice')
        ->image('https://example.com/alice.jpg')
        ->imageWidth(40)
        ->maxImageHeight(64)
        ->render();

    expect($html)->toContain('width: 40px')
        ->and($html)->toContain('max-height: 64px')
        ->and($html)->toContain('max-width: 2rem');
});

it('forces square dimensions for circular content images', function (): void {
    $html = Content::make()
        ->title('Alice')
        ->image('https://example.com/alice.jpg')
        ->circularImage()
        ->render();

    expect($html)->toContain('width: 2rem')
        ->and($html)->toContain('height: 2rem')
        ->and($html)->toContain('max-width: 2rem')
        ->and($html)->toContain('max-height: 2rem');
});

it('keeps icon media size class for prefix icons', function (): void {
    $html = Content::make()
        ->title('Alice')
        ->prefixIcon('heroicon-o-user')
        ->render();

    expect($html)->toContain('h-8 w-8');
});

it('defaults option images to max width and height of 2rem', function (): void {
    $html = Option::make(1)
        ->title('Alice')
        ->image('https://example.com/alice.jpg')
        ->render();

    expect($html)->toContain('max-width: 2rem')
        ->and($html)->toContain('max-height: 2rem');
});

it('applies explicit option image dimensions', function (): void {
    $html = Option::make(1)
        ->title('Alice')
        ->image('https://example.com/alice.jpg')
        ->imageSize(32)
        ->maxImageWidth(48)
        ->render();

    expect($html)->toContain('width: 32px')
        ->and($html)->toContain('height: 32px')
        ->and($html)->toContain('max-width: 48px')
        ->and($html)->toContain('max-height: 2rem');
});
