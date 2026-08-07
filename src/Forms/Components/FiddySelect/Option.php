<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Forms\Components\FiddySelect;

use BackedEnum;
use Closure;
use Filament\Support\Enums\IconSize;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Traits\Conditionable;

use function Filament\Support\generate_icon_html;

final class Option
{
    use Conditionable;

    protected string | int | null $value = null;

    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $hint = null;

    protected ?string $prefixImage = null;

    protected ?string $suffixImage = null;

    protected bool $circularImage = false;

    protected bool | Closure $isDisabled = false;

    protected string | Htmlable | null $tooltip = null;

    protected string | BackedEnum | Htmlable | null $prefixIcon = null;

    protected string | BackedEnum | Htmlable | null $suffixIcon = null;

    protected string | BackedEnum | Htmlable | null $titlePrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $titleSuffixIcon = null;

    protected string | BackedEnum | Htmlable | null $descriptionPrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $descriptionSuffixIcon = null;

    protected string | BackedEnum | Htmlable | null $hintPrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $hintSuffixIcon = null;

    public static function make(string | int | null $value = null): self
    {
        $option = new self;
        $option->value = $value;

        return $option;
    }

    public function value(string | int | null $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): string | int | null
    {
        return $this->value;
    }

    public function title(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function description(
        ?string $description,
        string | BackedEnum | Htmlable | null $icon = null,
    ): self {
        $this->description = $description;

        if (filled($icon)) {
            $this->descriptionIcon($icon);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function hint(
        ?string $hint,
        string | BackedEnum | Htmlable | null $icon = null,
    ): self {
        $this->hint = $hint;

        if (filled($icon)) {
            $this->hintIcon($icon);
        }

        return $this;
    }

    public function getHint(): ?string
    {
        return $this->hint;
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

    public function disabled(bool | Closure $condition = true): self
    {
        $this->isDisabled = $condition;

        return $this;
    }

    public function isDisabled(): bool
    {
        return (bool) value($this->isDisabled);
    }

    public function tooltip(string | Htmlable | null $tooltip): self
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    public function getTooltip(): ?string
    {
        if ($this->tooltip instanceof Htmlable) {
            $plain = trim(strip_tags($this->tooltip->toHtml()));

            return filled($plain) ? $plain : null;
        }

        return filled($this->tooltip) ? $this->tooltip : null;
    }

    public function prefixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->prefixIcon = $icon;

        return $this;
    }

    public function getPrefixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->prefixIcon;
    }

    public function suffixIcon(string | BackedEnum | Htmlable | null $icon): self
    {
        $this->suffixIcon = $icon;

        return $this;
    }

    public function getSuffixIcon(): string | BackedEnum | Htmlable | null
    {
        return $this->suffixIcon;
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

    protected function getWrapperClass(): string
    {
        return 'flex w-full min-w-0 overflow-hidden items-center gap-2';
    }

    protected function getMediaClass(): string
    {
        return $this->isCircularImage() ? 'rounded-full object-cover' : 'rounded-md object-cover';
    }

    protected function getMediaSize(): string
    {
        return 'h-8 w-8';
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

    protected function getPrefixIconHtml(): ?Htmlable
    {
        if (filled($this->prefixImage)) {
            return null;
        }

        return $this->resolveIconHtml($this->prefixIcon);
    }

    protected function getSuffixIconHtml(): ?Htmlable
    {
        if (filled($this->suffixImage)) {
            return null;
        }

        return $this->resolveIconHtml($this->suffixIcon);
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

    public function render(): string
    {
        return view('fiddy::components.select-option', [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'hint' => $this->getHint(),
            'tooltip' => $this->getTooltip(),
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
            'wrapperClass' => $this->getWrapperClass(),
            'mediaClass' => $this->getMediaClass(),
            'mediaSize' => $this->getMediaSize(),
        ])->render();
    }
}
