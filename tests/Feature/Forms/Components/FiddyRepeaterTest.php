<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Forms\Components\FiddyRepeater;
use Bensondevs\Fiddy\Support\Content;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Resolve item labels from state without Livewire / child-schema wiring.
 */
class TestableFiddyRepeater extends FiddyRepeater
{
    /**
     * @param  array<string, mixed>  $state
     */
    public function itemLabelFromState(array $state, string $key = 'item', ?int $index = null): string | Htmlable | null
    {
        return $this->resolveItemLabel([
            'container' => null,
            'item' => null,
            'key' => $key,
            'schema' => null,
            'state' => $state,
            'uuid' => $key,
            'index' => $index,
        ]);
    }
}

it('renders rich item labels with description and icons', function (): void {
    $repeater = TestableFiddyRepeater::make('products')
        ->itemLabel('Acme Widget')
        ->itemDescription('acme-widget')
        ->itemPrefixIcon(Heroicon::OutlinedCube)
        ->itemSuffixIcon(Heroicon::OutlinedArrowRight);

    $label = $repeater->itemLabelFromState([]);

    expect($label)->toBeInstanceOf(HtmlString::class)
        ->and((string) $label)->toContain('Acme Widget')
        ->and((string) $label)->toContain('acme-widget')
        ->and((string) $label)->toContain('fi-icon');
});

it('resolves label, description, icons, and color from closures with state', function (): void {
    $repeater = TestableFiddyRepeater::make('products')
        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
        ->itemDescription(fn (array $state): ?string => $state['slug'] ?? null)
        ->itemPrefixIcon(fn (array $state): Heroicon => $state['active']
            ? Heroicon::OutlinedCheckCircle
            : Heroicon::OutlinedXCircle)
        ->itemSuffixIcon(fn (array $state): Heroicon => Heroicon::OutlinedArrowRight)
        ->itemIconColor(fn (array $state): string => $state['active'] ? 'success' : 'danger');

    $label = $repeater->itemLabelFromState([
        'title' => 'Shipped Product',
        'slug' => 'shipped-product',
        'active' => true,
    ]);

    expect($label)->toBeInstanceOf(HtmlString::class)
        ->and((string) $label)->toContain('Shipped Product')
        ->and((string) $label)->toContain('shipped-product')
        ->and((string) $label)->toContain('fi-color-success');
});

it('applies itemIconColor to prefix and suffix icons', function (): void {
    $repeater = TestableFiddyRepeater::make('products')
        ->itemLabel('Colored')
        ->itemPrefixIcon(Heroicon::OutlinedCube)
        ->itemSuffixIcon(Heroicon::OutlinedArrowRight)
        ->itemIconColor('success');

    $html = (string) $repeater->itemLabelFromState([]);

    expect($html)->toContain('fi-color-success');
});

it('returns a plain string when only itemLabel is set', function (): void {
    $repeater = TestableFiddyRepeater::make('products')
        ->itemLabel(fn (array $state): string => $state['title']);

    $label = $repeater->itemLabelFromState(['title' => 'Plain Title']);

    expect($label)->toBe('Plain Title');
});

it('reports hasItemLabels when only description or prefix icon is set', function (): void {
    expect(FiddyRepeater::make('products')->itemDescription('slug')->hasItemLabels())->toBeTrue()
        ->and(FiddyRepeater::make('products')->itemPrefixIcon(Heroicon::OutlinedCube)->hasItemLabels())->toBeTrue()
        ->and(FiddyRepeater::make('products')->hasItemLabels())->toBeFalse();
});

it('colors Content prefix and suffix icons via iconColor', function (): void {
    $html = Content::make()
        ->title('Product')
        ->prefixIcon(Heroicon::OutlinedCube)
        ->suffixIcon(Heroicon::OutlinedArrowRight)
        ->iconColor('success')
        ->render();

    expect($html)->toContain('Product')
        ->and($html)->toContain('fi-color-success');
});
