<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Forms\Components\FiddyNumericInput;
use Filament\Support\RawJs;

it('applies a default thousand-dot money mask and strips dots from state', function (): void {
    $input = FiddyNumericInput::make('amount');

    $mask = $input->getMask();

    expect($mask)->toBeInstanceOf(RawJs::class)
        ->and((string) $mask)->toContain('$money($input')
        ->and((string) $mask)->toContain("'.'")
        ->and((string) $mask)->toContain(', 0)')
        ->and($input->getStripCharacters())->toContain('.')
        ->and($input->isNumeric())->toBeTrue();
});

it('configures money masking from Squire currency data for IDR', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->money(currency: 'IDR');

    $mask = (string) $input->getMask();

    expect($mask)->toContain('$money($input')
        ->and($input->getFiddyThousandsSeparator())->toBe('.')
        ->and($input->getFiddyDecimalSeparator())->toBe(',')
        ->and($input->getFiddyDecimalPlaces())->toBe(0)
        ->and($input->getStripCharacters())->toContain('.')
        ->and($input->getPrefixLabel())->toBe('Rp');
});

it('configures money masking from Squire currency data for USD', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->money(currency: 'USD');

    expect($input->getFiddyThousandsSeparator())->toBe(',')
        ->and($input->getFiddyDecimalSeparator())->toBe('.')
        ->and($input->getFiddyDecimalPlaces())->toBe(2)
        ->and($input->getStripCharacters())->toContain(',')
        ->and($input->getPrefixLabel())->toBe('$');
});

it('configures euro separators via akaunting currency formatting', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->money(currency: 'EUR');

    expect($input->getFiddyThousandsSeparator())->toBe('.')
        ->and($input->getFiddyDecimalSeparator())->toBe(',')
        ->and($input->getFiddyDecimalPlaces())->toBe(2)
        ->and($input->getPrefixLabel())->toBe('€');
});

it('allows overriding decimal places after money()', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->money(currency: 'IDR')
        ->decimalPlaces(2);

    expect($input->getFiddyDecimalPlaces())->toBe(2)
        ->and((string) $input->getMask())->toContain(', 2)');
});

it('strips thousand separators from dehydrated-looking state', function (): void {
    $input = FiddyNumericInput::make('amount');

    $method = new ReflectionMethod($input, 'stripCharactersFromState');
    $method->setAccessible(true);

    expect($method->invoke($input, '1.000.000'))->toBe('1000000');
});

it('rejects unknown currency codes', function (): void {
    FiddyNumericInput::make('amount')
        ->money(currency: 'ZZZ');
})->throws(InvalidArgumentException::class);

it('spells the amount as live helper text', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->spellAmount(locale: 'en');

    expect($input->isLive())->toBeTrue()
        ->and($input->resolveSpelledAmount(10000))->toBe('Ten thousand')
        ->and($input->resolveSpelledAmount(null))->toBeNull()
        ->and($input->resolveSpelledAmount(''))->toBeNull();
});

it('spells USD fractional amounts with currency subunits in English', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->money(currency: 'USD')
        ->spellAmount(locale: 'en');

    expect($input->resolveSpelledAmount(9.5))->toBe('Nine and fifty cents')
        ->and($input->resolveSpelledAmount(9.50))->toBe('Nine and fifty cents')
        ->and($input->resolveSpelledAmount(9.00))->toBe('Nine')
        ->and($input->resolveSpelledAmount(0.50))->toBe('Fifty cents')
        ->and($input->resolveSpelledAmount(1.01))->toBe('One and one cent');
});

it('spells USD fractional amounts with currency subunits in Indonesian', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->money(currency: 'USD')
        ->spellAmount(locale: 'id');

    $spelled = $input->resolveSpelledAmount(9.50);

    expect($spelled)->toContain('lima puluh')
        ->and($spelled)->toContain('sen')
        ->and($input->resolveSpelledAmount(9.00))->not->toContain('sen');
});

it('normalizes localized decimal strings before spelling', function (): void {
    $input = FiddyNumericInput::make('amount')
        ->money(currency: 'EUR')
        ->spellAmount(locale: 'en');

    expect($input->resolveSpelledAmount('9,50'))->toBe('Nine and fifty cents');
});

it('skips spellAmount configuration when condition is false', function (): void {
    $input = FiddyNumericInput::make('amount');

    expect($input->spellAmount(false))->toBe($input);
});
