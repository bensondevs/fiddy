<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Forms\Components\FiddySelect;

use BackedEnum;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

final class OptionGuesser
{
    /**
     * @var list<string>
     */
    public const TITLE_ATTRIBUTES = ['name', 'title'];

    /**
     * @var list<string>
     */
    public const DESCRIPTION_ATTRIBUTES = ['email', 'description'];

    /**
     * @var list<string>
     */
    public const HINT_ATTRIBUTES = ['phone', 'hint'];

    /**
     * @var list<string>
     */
    public const ATTRIBUTE_KEYS = [
        'name',
        'title',
        'email',
        'description',
        'phone',
        'hint',
        'image',
        'prefix_image',
        'suffix_image',
        'icon',
        'prefix_icon',
        'suffix_icon',
        'title_icon',
        'title_prefix_icon',
        'title_suffix_icon',
        'description_icon',
        'description_prefix_icon',
        'description_suffix_icon',
        'hint_icon',
        'hint_prefix_icon',
        'hint_suffix_icon',
    ];

    private const HAS_MEDIA_INTERFACE = 'Spatie\\MediaLibrary\\HasMedia';

    /**
     * @param  Model|array<string, mixed>|UnitEnum  $data
     */
    public static function from(Model | array | UnitEnum $data, string | int | null $value = null): ?Option
    {
        if ($data instanceof UnitEnum) {
            return self::fromEnum($data);
        }

        $title = self::firstFilled($data, self::TITLE_ATTRIBUTES);
        $description = self::firstFilled($data, self::DESCRIPTION_ATTRIBUTES);
        $hint = self::firstFilled($data, self::HINT_ATTRIBUTES);
        $prefixImage = self::resolvePrefixImage($data);
        $suffixImage = is_array($data) ? (data_get($data, 'suffix_image') ?: null) : null;
        $suffixImage = filled($suffixImage) ? (is_string($suffixImage) ? $suffixImage : (string) $suffixImage) : null;

        if (
            blank($title)
            && blank($description)
            && blank($hint)
            && blank($prefixImage)
            && blank($suffixImage)
            && ! self::hasAnyIconKey($data)
        ) {
            return null;
        }

        if ($value === null && $data instanceof Model) {
            $value = $data->getKey();
        }

        $option = Option::make($value)
            ->title($title)
            ->description($description)
            ->hint($hint)
            ->prefixImage($prefixImage)
            ->suffixImage($suffixImage);

        return self::applyIcons($option, $data);
    }

    public static function fromEnum(UnitEnum $case): Option
    {
        if ($case instanceof FiddyComponentsPresentable) {
            return $case->asOption();
        }

        return self::fromEnumContracts($case);
    }

    /**
     * Map Filament HasLabel / HasIcon / HasDescription onto an Option (no presentable path).
     * Presentable enums can call this inside asOption() to reuse contract defaults.
     */
    public static function fromEnumContracts(UnitEnum $case): Option
    {
        $option = Option::make(self::enumValue($case))
            ->title(self::enumLabel($case));

        if ($case instanceof HasDescription) {
            $description = self::stringifyLabel($case->getDescription());

            if (filled($description)) {
                $option->description($description);
            }
        }

        if ($case instanceof HasIcon) {
            $icon = $case->getIcon();

            if (filled($icon)) {
                $option->icon($icon);
            }
        }

        return $option;
    }

    public static function enumValue(UnitEnum $case): string | int
    {
        if ($case instanceof BackedEnum) {
            return $case->value;
        }

        return $case->name;
    }

    public static function enumLabel(UnitEnum $case): string
    {
        if ($case instanceof HasLabel) {
            $label = self::stringifyLabel($case->getLabel());

            if (filled($label)) {
                return $label;
            }
        }

        return $case->name;
    }

    /**
     * @param  class-string<UnitEnum>  $enumClass
     */
    public static function enumCase(string $enumClass, mixed $value): ?UnitEnum
    {
        if (! enum_exists($enumClass)) {
            return null;
        }

        if (is_a($enumClass, BackedEnum::class, allow_string: true)) {
            if ($value instanceof BackedEnum) {
                return $value instanceof $enumClass ? $value : null;
            }

            /** @var class-string<BackedEnum> $enumClass */
            $case = $enumClass::tryFrom($value);

            if ($case instanceof UnitEnum) {
                return $case;
            }
        }

        foreach ($enumClass::cases() as $case) {
            if ((string) self::enumValue($case) === (string) $value) {
                return $case;
            }
        }

        return null;
    }

    public static function stringifyLabel(string | Htmlable | null $label): ?string
    {
        if ($label instanceof Htmlable) {
            $plain = trim(strip_tags($label->toHtml()));

            return filled($plain) ? $plain : null;
        }

        return filled($label) ? $label : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function looksLikeOptionAttributes(array $data): bool
    {
        foreach (self::ATTRIBUTE_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Model|array<string, mixed>  $data
     */
    protected static function hasAnyIconKey(Model | array $data): bool
    {
        foreach (self::ATTRIBUTE_KEYS as $key) {
            if (str_contains($key, 'icon') && filled(data_get($data, $key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Model|array<string, mixed>  $data
     */
    protected static function applyIcons(Option $option, Model | array $data): Option
    {
        $prefixIcon = data_get($data, 'prefix_icon') ?? data_get($data, 'icon');
        $suffixIcon = data_get($data, 'suffix_icon');

        if (filled($prefixIcon)) {
            $option->prefixIcon($prefixIcon);
        }

        if (filled($suffixIcon)) {
            $option->suffixIcon($suffixIcon);
        }

        $titlePrefix = data_get($data, 'title_prefix_icon') ?? data_get($data, 'title_icon');
        $titleSuffix = data_get($data, 'title_suffix_icon');

        if (filled($titlePrefix)) {
            $option->titlePrefixIcon($titlePrefix);
        }

        if (filled($titleSuffix)) {
            $option->titleSuffixIcon($titleSuffix);
        }

        $descriptionPrefix = data_get($data, 'description_prefix_icon') ?? data_get($data, 'description_icon');
        $descriptionSuffix = data_get($data, 'description_suffix_icon');

        if (filled($descriptionPrefix)) {
            $option->descriptionPrefixIcon($descriptionPrefix);
        }

        if (filled($descriptionSuffix)) {
            $option->descriptionSuffixIcon($descriptionSuffix);
        }

        $hintPrefix = data_get($data, 'hint_prefix_icon') ?? data_get($data, 'hint_icon');
        $hintSuffix = data_get($data, 'hint_suffix_icon');

        if (filled($hintPrefix)) {
            $option->hintPrefixIcon($hintPrefix);
        }

        if (filled($hintSuffix)) {
            $option->hintSuffixIcon($hintSuffix);
        }

        return $option;
    }

    /**
     * @param  Model|array<string, mixed>  $data
     * @param  list<string>  $attributes
     */
    protected static function firstFilled(Model | array $data, array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            $value = data_get($data, $attribute);

            if (filled($value)) {
                return is_string($value) ? $value : (string) $value;
            }
        }

        return null;
    }

    /**
     * @param  Model|array<string, mixed>  $data
     */
    protected static function resolvePrefixImage(Model | array $data): ?string
    {
        if (is_array($data)) {
            $image = data_get($data, 'prefix_image') ?? data_get($data, 'image');

            if (filled($image)) {
                return is_string($image) ? $image : (string) $image;
            }

            return null;
        }

        return self::resolveImage($data);
    }

    /**
     * @param  Model|array<string, mixed>  $data
     */
    protected static function resolveImage(Model | array $data): ?string
    {
        if (! $data instanceof Model) {
            return null;
        }

        if (! interface_exists(self::HAS_MEDIA_INTERFACE) || ! is_a($data, self::HAS_MEDIA_INTERFACE)) {
            return null;
        }

        /** @var object{getFirstMediaUrl: callable(): string} $data */
        $url = $data->getFirstMediaUrl();

        return filled($url) ? $url : null;
    }
}
