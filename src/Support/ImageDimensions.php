<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Support;

final class ImageDimensions
{
    public static function normalize(int | string | null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return "{$value}px";
        }

        return $value;
    }

    /**
     * @return array<string, string>
     */
    public static function styles(
        ?string $width = null,
        ?string $height = null,
        ?string $maxWidth = null,
        ?string $maxHeight = null,
    ): array {
        $styles = [];

        if (filled($width)) {
            $styles['width'] = $width;
        }

        if (filled($height)) {
            $styles['height'] = $height;
        }

        if (filled($maxWidth)) {
            $styles['max-width'] = $maxWidth;
        }

        if (filled($maxHeight)) {
            $styles['max-height'] = $maxHeight;
        }

        return $styles;
    }

    /**
     * @param  array<string, string>  $styles
     */
    public static function toInlineStyle(array $styles): string
    {
        return collect($styles)
            ->map(fn (string $value, string $property): string => "{$property}: {$value}")
            ->implode('; ');
    }
}
