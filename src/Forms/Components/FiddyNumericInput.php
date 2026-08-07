<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Forms\Components;

use Akaunting\Money\Currency as MoneyCurrency;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\RawJs;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Squire\Models\Currency as SquireCurrency;

class FiddyNumericInput extends TextInput
{
    protected int | Closure | null $fiddyDecimalPlaces = 0;

    protected string | Closure | null $fiddyThousandsSeparator = '.';

    protected string | Closure | null $fiddyDecimalSeparator = ',';

    protected string | Closure | null $fiddyCurrencyCode = null;

    protected string | Closure | null $spellAmountLocale = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->numeric()
            ->applyNumericMask();
    }

    public function money(
        string | Closure | null $currency = 'IDR',
        ?int $decimalPlaces = null,
    ): static {
        $code = strtolower((string) ($this->evaluate($currency) ?? 'IDR'));

        $squireCurrency = SquireCurrency::find($code);

        if (! $squireCurrency instanceof SquireCurrency) {
            throw new InvalidArgumentException(
                "Unknown currency [{$code}]. Install squirephp/currencies-en and use a valid ISO 4217 code.",
            );
        }

        $alphabetic = strtoupper((string) ($squireCurrency->code_alphabetic ?: $code));
        $moneyCurrency = new MoneyCurrency($alphabetic);

        $this->fiddyCurrencyCode = $alphabetic;
        $this->fiddyDecimalPlaces = $decimalPlaces ?? (int) $squireCurrency->decimal_digits;
        $this->fiddyThousandsSeparator = $moneyCurrency->getThousandsSeparator();
        $this->fiddyDecimalSeparator = $moneyCurrency->getDecimalMark();

        $symbol = $squireCurrency->symbol_native ?: $squireCurrency->symbol;

        $this
            ->prefix(filled($symbol) ? $symbol : $alphabetic)
            ->applyNumericMask();

        return $this;
    }

    public function spellAmount(
        bool | Closure $condition = true,
        string | Closure | null $locale = null,
    ): static {
        if (! $this->evaluate($condition)) {
            return $this;
        }

        $this->spellAmountLocale = $locale;

        return $this
            ->live(debounce: 500)
            ->helperText(function (FiddyNumericInput $component, Get $get): ?string {
                return $component->resolveSpelledAmount($get($component->getName()));
            });
    }

    public function resolveSpelledAmount(mixed $amount): ?string
    {
        $normalized = $this->normalizeSpelledAmount($amount);

        if ($normalized === null) {
            return null;
        }

        $locale = $this->evaluate($this->spellAmountLocale) ?? app()->getLocale();
        $locale = is_string($locale) ? $locale : app()->getLocale();

        $places = $this->getFiddyDecimalPlaces();
        $absolute = abs($normalized);
        $factor = 10 ** $places;
        $totalMinor = (int) round($absolute * $factor);
        $major = intdiv($totalMinor, max($factor, 1));
        $minor = $places > 0 ? $totalMinor % $factor : 0;

        $parts = [];

        if ($major > 0 || $minor === 0) {
            $spelledMajor = Number::spell($major, locale: $locale);

            if (! is_string($spelledMajor)) {
                return null;
            }

            $parts[] = $spelledMajor;
        }

        if ($minor > 0) {
            $spelledMinor = Number::spell($minor, locale: $locale);

            if (! is_string($spelledMinor)) {
                return null;
            }

            $subunit = $this->resolveSubunitLabel($minor, $locale);

            if ($major > 0) {
                $parts[] = __('fiddy::spell.and', locale: $locale);
            }

            $parts[] = $spelledMinor;
            $parts[] = $subunit;
        }

        return Str::ucfirst(implode(' ', $parts));
    }

    public function decimalPlaces(int | Closure | null $places): static
    {
        $this->fiddyDecimalPlaces = $places;

        return $this->applyNumericMask();
    }

    public function thousandsSeparator(string | Closure | null $separator): static
    {
        $this->fiddyThousandsSeparator = $separator;

        return $this->applyNumericMask();
    }

    public function decimalSeparator(string | Closure | null $separator): static
    {
        $this->fiddyDecimalSeparator = $separator;

        return $this->applyNumericMask();
    }

    public function getFiddyDecimalPlaces(): int
    {
        return (int) ($this->evaluate($this->fiddyDecimalPlaces) ?? 0);
    }

    public function getFiddyThousandsSeparator(): string
    {
        return (string) ($this->evaluate($this->fiddyThousandsSeparator) ?? '.');
    }

    public function getFiddyDecimalSeparator(): string
    {
        return (string) ($this->evaluate($this->fiddyDecimalSeparator) ?? ',');
    }

    public function getFiddyCurrencyCode(): ?string
    {
        $code = $this->evaluate($this->fiddyCurrencyCode);

        return filled($code) ? strtoupper((string) $code) : null;
    }

    protected function normalizeSpelledAmount(mixed $amount): ?float
    {
        if (! filled($amount)) {
            return null;
        }

        if (is_int($amount) || is_float($amount)) {
            return (float) $amount;
        }

        if (! is_string($amount) && ! is_numeric($amount)) {
            return null;
        }

        $string = trim((string) $amount);

        if ($string === '') {
            return null;
        }

        if (is_numeric($string)) {
            return (float) $string;
        }

        $thousands = $this->getFiddyThousandsSeparator();
        $decimal = $this->getFiddyDecimalSeparator();

        if ($thousands !== '') {
            $string = str_replace($thousands, '', $string);
        }

        if ($decimal !== '.' && $decimal !== '') {
            $string = str_replace($decimal, '.', $string);
        }

        return is_numeric($string) ? (float) $string : null;
    }

    protected function resolveSubunitLabel(int $minor, string $locale): string
    {
        $code = $this->getFiddyCurrencyCode() ?? 'default';
        $key = $minor === 1 ? 'one' : 'other';

        $specific = __('fiddy::spell.subunits.' . $code . '.' . $key, locale: $locale);

        if ($specific !== 'fiddy::spell.subunits.' . $code . '.' . $key) {
            return $specific;
        }

        return __('fiddy::spell.subunits.default.' . $key, locale: $locale);
    }

    protected function applyNumericMask(): static
    {
        $decimalSeparator = $this->getFiddyDecimalSeparator();
        $thousandsSeparator = $this->getFiddyThousandsSeparator();
        $decimalPlaces = $this->getFiddyDecimalPlaces();

        $this
            ->mask(RawJs::make(sprintf(
                '$money($input, \'%s\', \'%s\', %d)',
                addcslashes($decimalSeparator, "'\\"),
                addcslashes($thousandsSeparator, "'\\"),
                $decimalPlaces,
            )))
            ->stripCharacters(array_values(array_unique(array_filter([
                $thousandsSeparator,
            ]))));

        unset($this->cachedStripCharacters);

        return $this;
    }
}
