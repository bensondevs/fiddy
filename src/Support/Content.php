<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Support;

use BackedEnum;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\OptionGuesser;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Enums\IconSize;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Conditionable;
use UnitEnum;

use function Filament\Support\generate_icon_html;

final class Content
{
    use Conditionable;

    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $prefixImage = null;

    protected ?string $suffixImage = null;

    protected bool $circularImage = false;

    protected string | BackedEnum | Htmlable | null $prefixIcon = null;

    protected string | BackedEnum | Htmlable | null $suffixIcon = null;

    protected string | BackedEnum | Htmlable | null $titlePrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $titleSuffixIcon = null;

    protected string | BackedEnum | Htmlable | null $descriptionPrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $descriptionSuffixIcon = null;

    protected ?string $hint = null;

    protected string | BackedEnum | Htmlable | null $hintPrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $hintSuffixIcon = null;

    protected ?string $aboveTitle = null;

    protected string | BackedEnum | Htmlable | null $aboveTitlePrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $aboveTitleSuffixIcon = null;

    protected ?string $aboveDescription = null;

    protected string | BackedEnum | Htmlable | null $aboveDescriptionPrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $aboveDescriptionSuffixIcon = null;

    protected ?string $titleAttribute = null;

    protected ?string $descriptionAttribute = null;

    /**
     * @var list<string>
     */
    public const FALLBACK_SEARCH_ATTRIBUTES = ['name', 'title', 'email', 'description'];

    /**
     * @var list<string>
     */
    public const FALLBACK_SORT_ATTRIBUTES = ['name', 'title'];

    public static function make(): self
    {
        return new self;
    }

    public static function guess(Model $record): ?self
    {
        $titleAttribute = self::firstFilledAttribute($record, OptionGuesser::TITLE_ATTRIBUTES);
        $descriptionAttribute = self::firstFilledAttribute($record, OptionGuesser::DESCRIPTION_ATTRIBUTES);
        $hintAttribute = self::firstFilledAttribute($record, OptionGuesser::HINT_ATTRIBUTES);
        $image = self::resolveImage($record);

        $title = filled($titleAttribute) ? self::stringValue(data_get($record, $titleAttribute)) : null;
        $description = filled($descriptionAttribute) ? self::stringValue(data_get($record, $descriptionAttribute)) : null;
        $hint = filled($hintAttribute) ? self::stringValue(data_get($record, $hintAttribute)) : null;

        if (blank($title) && blank($description) && blank($hint) && blank($image)) {
            return null;
        }

        $content = self::make();

        if (filled($title)) {
            $content->title($title, $titleAttribute);
        }

        if (filled($description)) {
            $content->description($description, $descriptionAttribute);
        }

        if (filled($hint)) {
            $content->hint($hint);
        }

        if (filled($image)) {
            $content->prefixImage($image);
        }

        return $content;
    }

    public static function fromEnum(UnitEnum $case): self
    {
        if ($case instanceof FiddyComponentsPresentable) {
            return $case->asColumnContent();
        }

        return self::fromEnumContracts($case);
    }

    /**
     * Map Filament HasLabel / HasIcon / HasDescription onto Content (no presentable path).
     */
    public static function fromEnumContracts(UnitEnum $case): self
    {
        $content = self::make()->title(OptionGuesser::enumLabel($case));

        if ($case instanceof HasDescription) {
            $description = OptionGuesser::stringifyLabel($case->getDescription());

            if (filled($description)) {
                $content->description($description);
            }
        }

        if ($case instanceof HasIcon) {
            $icon = $case->getIcon();

            if (filled($icon)) {
                $content->icon($icon);
            }
        }

        return $content;
    }

    public function title(?string $title, ?string $attribute = null): self
    {
        $this->title = $title;

        if (filled($attribute)) {
            $this->titleAttribute = $attribute;
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getTitleAttribute(): ?string
    {
        return $this->titleAttribute;
    }

    public function description(?string $description, ?string $attribute = null): self
    {
        $this->description = $description;

        if (filled($attribute)) {
            $this->descriptionAttribute = $attribute;
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->descriptionAttribute;
    }

    public function prefixImage(?string $image): self
    {
        $this->prefixImage = $image;

        return $this;
    }

    public function getPrefixImage(): ?string
    {
        return $this->prefixImage;
    }

    public function suffixImage(?string $image): self
    {
        $this->suffixImage = $image;

        return $this;
    }

    public function getSuffixImage(): ?string
    {
        return $this->suffixImage;
    }

    public function image(?string $image): self
    {
        return $this->prefixImage($image);
    }

    public function getImage(): ?string
    {
        return $this->prefixImage;
    }

    public function circularImage(bool $circular = true): self
    {
        $this->circularImage = $circular;

        return $this;
    }

    public function isCircularImage(): bool
    {
        return $this->circularImage;
    }

    public function prefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->prefixIcon = $icon;

        return $this;
    }

    public function suffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->suffixIcon = $icon;

        return $this;
    }

    public function icon(string | BackedEnum | Htmlable | null $icon): self
    {
        return $this->prefixIcon($icon);
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->prefixIcon;
    }

    public function titlePrefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->titlePrefixIcon = $icon;

        return $this;
    }

    public function getTitlePrefixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->titlePrefixIcon;
    }

    public function titleSuffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->titleSuffixIcon = $icon;

        return $this;
    }

    public function getTitleSuffixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->titleSuffixIcon;
    }

    public function titleIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        return $this->titlePrefixIcon($icon);
    }

    public function descriptionPrefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->descriptionPrefixIcon = $icon;

        return $this;
    }

    public function getDescriptionPrefixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->descriptionPrefixIcon;
    }

    public function descriptionSuffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->descriptionSuffixIcon = $icon;

        return $this;
    }

    public function getDescriptionSuffixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->descriptionSuffixIcon;
    }

    public function descriptionIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        return $this->descriptionPrefixIcon($icon);
    }

    public function hint(?string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    public function getHint(): ?string
    {
        return $this->hint;
    }

    public function hintPrefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->hintPrefixIcon = $icon;

        return $this;
    }

    public function getHintPrefixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->hintPrefixIcon;
    }

    public function hintSuffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->hintSuffixIcon = $icon;

        return $this;
    }

    public function getHintSuffixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->hintSuffixIcon;
    }

    public function hintIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        return $this->hintPrefixIcon($icon);
    }

    public function aboveTitle(?string $aboveTitle): self
    {
        $this->aboveTitle = $aboveTitle;

        return $this;
    }

    public function getAboveTitle(): ?string
    {
        return $this->aboveTitle;
    }

    public function aboveTitlePrefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->aboveTitlePrefixIcon = $icon;

        return $this;
    }

    public function aboveTitleSuffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->aboveTitleSuffixIcon = $icon;

        return $this;
    }

    public function aboveDescription(?string $aboveDescription): self
    {
        $this->aboveDescription = $aboveDescription;

        return $this;
    }

    public function getAboveDescription(): ?string
    {
        return $this->aboveDescription;
    }

    public function aboveDescriptionPrefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->aboveDescriptionPrefixIcon = $icon;

        return $this;
    }

    public function aboveDescriptionSuffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->aboveDescriptionSuffixIcon = $icon;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getSearchAttributes(): array
    {
        return array_values(array_unique(array_filter([
            $this->titleAttribute,
            $this->descriptionAttribute,
        ])));
    }

    public function getSortAttribute(): ?string
    {
        return $this->titleAttribute ?? $this->descriptionAttribute;
    }

    /**
     * @return list<string>
     */
    public function getFilterAttributes(): array
    {
        return $this->getSearchAttributes();
    }

    protected function getWrapperClass(): string
    {
        return 'flex w-full items-center gap-2';
    }

    protected function getMediaClass(): string
    {
        return $this->isCircularImage() ? 'rounded-full object-cover' : 'rounded-md object-cover';
    }

    protected function getMediaSize(): string
    {
        return 'h-8 w-8';
    }

    protected function getPrefixIconHtml(): ?Htmlable
    {
        if (filled($this->prefixImage) || blank($this->prefixIcon)) {
            return null;
        }

        return $this->resolveIconHtml($this->prefixIcon);
    }

    protected function getSuffixIconHtml(): ?Htmlable
    {
        if (filled($this->suffixImage) || blank($this->suffixIcon)) {
            return null;
        }

        return $this->resolveIconHtml($this->suffixIcon);
    }

    protected function resolveIconHtml(
        string | BackedEnum | Htmlable | null $icon,
        ?IconSize $size = null,
    ): ?Htmlable {
        if (blank($icon)) {
            return null;
        }

        return generate_icon_html($icon, size: $size);
    }

    protected function getTitlePrefixIconHtml(): ?Htmlable
    {
        if (blank($this->title)) {
            return null;
        }

        return $this->resolveIconHtml($this->titlePrefixIcon, IconSize::ExtraSmall);
    }

    protected function getTitleSuffixIconHtml(): ?Htmlable
    {
        if (blank($this->title)) {
            return null;
        }

        return $this->resolveIconHtml($this->titleSuffixIcon, IconSize::ExtraSmall);
    }

    protected function getDescriptionPrefixIconHtml(): ?Htmlable
    {
        if (blank($this->description)) {
            return null;
        }

        return $this->resolveIconHtml($this->descriptionPrefixIcon, IconSize::ExtraSmall);
    }

    protected function getDescriptionSuffixIconHtml(): ?Htmlable
    {
        if (blank($this->description)) {
            return null;
        }

        return $this->resolveIconHtml($this->descriptionSuffixIcon, IconSize::ExtraSmall);
    }

    protected function getHintPrefixIconHtml(): ?Htmlable
    {
        if (blank($this->hint)) {
            return null;
        }

        return $this->resolveIconHtml($this->hintPrefixIcon, IconSize::ExtraSmall);
    }

    protected function getHintSuffixIconHtml(): ?Htmlable
    {
        if (blank($this->hint)) {
            return null;
        }

        return $this->resolveIconHtml($this->hintSuffixIcon, IconSize::ExtraSmall);
    }

    protected function getAboveTitlePrefixIconHtml(): ?Htmlable
    {
        if (blank($this->aboveTitle)) {
            return null;
        }

        return $this->resolveIconHtml($this->aboveTitlePrefixIcon, IconSize::ExtraSmall);
    }

    protected function getAboveTitleSuffixIconHtml(): ?Htmlable
    {
        if (blank($this->aboveTitle)) {
            return null;
        }

        return $this->resolveIconHtml($this->aboveTitleSuffixIcon, IconSize::ExtraSmall);
    }

    protected function getAboveDescriptionPrefixIconHtml(): ?Htmlable
    {
        if (blank($this->aboveDescription)) {
            return null;
        }

        return $this->resolveIconHtml($this->aboveDescriptionPrefixIcon, IconSize::ExtraSmall);
    }

    protected function getAboveDescriptionSuffixIconHtml(): ?Htmlable
    {
        if (blank($this->aboveDescription)) {
            return null;
        }

        return $this->resolveIconHtml($this->aboveDescriptionSuffixIcon, IconSize::ExtraSmall);
    }

    public function render(): string
    {
        return view('fiddy::components.select-option', [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'hint' => $this->getHint(),
            'aboveTitle' => $this->getAboveTitle(),
            'aboveDescription' => $this->getAboveDescription(),
            'prefixImage' => $this->getPrefixImage(),
            'suffixImage' => $this->getSuffixImage(),
            'circularImage' => $this->isCircularImage(),
            'prefixIconHtml' => $this->getPrefixIconHtml(),
            'suffixIconHtml' => $this->getSuffixIconHtml(),
            'titlePrefixIconHtml' => $this->getTitlePrefixIconHtml(),
            'titleSuffixIconHtml' => $this->getTitleSuffixIconHtml(),
            'descriptionPrefixIconHtml' => $this->getDescriptionPrefixIconHtml(),
            'descriptionSuffixIconHtml' => $this->getDescriptionSuffixIconHtml(),
            'hintPrefixIconHtml' => $this->getHintPrefixIconHtml(),
            'hintSuffixIconHtml' => $this->getHintSuffixIconHtml(),
            'aboveTitlePrefixIconHtml' => $this->getAboveTitlePrefixIconHtml(),
            'aboveTitleSuffixIconHtml' => $this->getAboveTitleSuffixIconHtml(),
            'aboveDescriptionPrefixIconHtml' => $this->getAboveDescriptionPrefixIconHtml(),
            'aboveDescriptionSuffixIconHtml' => $this->getAboveDescriptionSuffixIconHtml(),
            'wrapperClass' => $this->getWrapperClass(),
            'mediaClass' => $this->getMediaClass(),
            'mediaSize' => $this->getMediaSize(),
        ])->render();
    }

    /**
     * @param  list<string>  $attributes
     */
    protected static function firstFilledAttribute(Model $record, array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            if (filled(data_get($record, $attribute))) {
                return $attribute;
            }
        }

        return null;
    }

    protected static function stringValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return is_string($value) ? $value : (string) $value;
    }

    public static function resolveImageUrl(Model $record): ?string
    {
        $avatarUrl = $record->getAttribute('avatar_url');

        if (filled($avatarUrl)) {
            return (string) $avatarUrl;
        }

        return self::resolveImage($record);
    }

    protected static function resolveImage(Model $record): ?string
    {
        $hasMedia = 'Spatie\\MediaLibrary\\HasMedia';

        if (! interface_exists($hasMedia) || ! is_a($record, $hasMedia)) {
            return null;
        }

        /** @var object{getFirstMediaUrl: callable(): string} $record */
        $url = $record->getFirstMediaUrl();

        return filled($url) ? $url : null;
    }
}
