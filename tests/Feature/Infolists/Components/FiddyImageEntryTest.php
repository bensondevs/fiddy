<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Infolists\Components\FiddyImageEntry;

/**
 * Avoid Filament entry-wrapper / Livewire container wiring for unit HTML asserts.
 */
class TestableFiddyImageEntry extends FiddyImageEntry
{
    public function wrapEmbeddedHtml(string $html): string
    {
        return $html;
    }
}

it('renders an image from absolute URL state', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false)
        ->previewable(false);

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('https://example.com/header.jpg')
        ->and($html)->toContain('<img');
});

it('adds fi-rounded when rounded', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false)
        ->rounded()
        ->previewable(false);

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('fi-rounded')
        ->and($html)->not->toContain('fi-circular');
});

it('circular takes precedence over rounded', function (): void {
    $entry = TestableFiddyImageEntry::make('avatar')
        ->state('https://example.com/avatar.jpg')
        ->checkFileExistence(false)
        ->rounded()
        ->circular()
        ->previewable(false);

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('fi-circular')
        ->and($html)->not->toContain('fi-rounded');
});

it('includes Alpine preview overlay by default', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false);

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('fi-previewable')
        ->and($html)->toContain('previewUrl')
        ->and($html)->toContain('fi-in-image-preview-overlay')
        ->and($html)->toContain('fi-in-image-preview-trigger');
});

it('omits preview wiring when previewable is false', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false)
        ->previewable(false);

    $html = $entry->toEmbeddedHtml();

    expect($html)->not->toContain('fi-previewable')
        ->and($html)->not->toContain('fi-in-image-preview-overlay')
        ->and($html)->not->toContain('fi-in-image-preview-trigger');
});

it('skips preview when url is set', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false)
        ->url('https://example.com/full');

    $html = $entry->toEmbeddedHtml();

    expect($html)->not->toContain('fi-previewable')
        ->and($html)->not->toContain('fi-in-image-preview-overlay')
        ->and($html)->not->toContain('fi-in-image-preview-trigger');
});

it('uses a state-based url link instead of preview', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false)
        ->url(fn (string $state): string => $state);

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('<a ')
        ->and($html)->toContain('https://example.com/header.jpg')
        ->and($html)->not->toContain('fi-in-image-preview-trigger');
});

it('defaults to max width and height without a fixed height', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false)
        ->previewable(false);

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('max-width: 8rem')
        ->and($html)->toContain('max-height: 8rem')
        ->and($html)->not->toContain('style="height:')
        ->and($html)->not->toMatch('/(?<!max-)height:\s*8rem/');
});

it('applies explicit image height and max dimensions', function (): void {
    $entry = TestableFiddyImageEntry::make('header_image')
        ->state('https://example.com/header.jpg')
        ->checkFileExistence(false)
        ->previewable(false)
        ->imageHeight(80)
        ->maxImageWidth(200)
        ->maxImageHeight(120);

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('height: 80px')
        ->and($html)->toContain('max-width: 200px')
        ->and($html)->toContain('max-height: 120px');
});

it('forces equal width and height for circular images without explicit size', function (): void {
    $entry = TestableFiddyImageEntry::make('avatar')
        ->state('https://example.com/avatar.jpg')
        ->checkFileExistence(false)
        ->previewable(false)
        ->circular();

    $html = $entry->toEmbeddedHtml();

    expect($html)->toContain('width: 8rem')
        ->and($html)->toContain('height: 8rem')
        ->and($html)->toContain('max-width: 8rem')
        ->and($html)->toContain('max-height: 8rem');
});
