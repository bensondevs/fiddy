<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Concerns;

use Bensondevs\Fiddy\Support\ImageDimensions;

trait HasImageDimensions
{
    use HasMaxImageDimensions;

    protected int | string | null $imageWidth = null;

    protected int | string | null $imageHeight = null;

    public function imageWidth(int | string | null $width): static
    {
        $this->imageWidth = $width;

        return $this;
    }

    public function imageHeight(int | string | null $height): static
    {
        $this->imageHeight = $height;

        return $this;
    }

    public function imageSize(int | string $size): static
    {
        $this->imageWidth($size);
        $this->imageHeight($size);

        return $this;
    }

    public function getImageWidth(): ?string
    {
        return ImageDimensions::normalize($this->imageWidth);
    }

    public function getImageHeight(): ?string
    {
        return ImageDimensions::normalize($this->imageHeight);
    }

    /**
     * @return array<string, string>
     */
    public function getImageDimensionStyles(): array
    {
        $width = $this->getImageWidth();
        $height = $this->getImageHeight();
        $maxWidth = $this->getMaxImageWidth();
        $maxHeight = $this->getMaxImageHeight();

        if ($this->shouldForceSquareImageDimensions() && blank($width) && blank($height)) {
            $size = $maxWidth ?? $maxHeight;
            $width = $size;
            $height = $size;
        }

        return ImageDimensions::styles($width, $height, $maxWidth, $maxHeight);
    }

    public function getMediaStyle(): string
    {
        return ImageDimensions::toInlineStyle($this->getImageDimensionStyles());
    }

    protected function shouldForceSquareImageDimensions(): bool
    {
        return $this->isCircularImage();
    }

    protected function defaultMaxImageWidth(): ?string
    {
        return '2rem';
    }

    protected function defaultMaxImageHeight(): ?string
    {
        return '2rem';
    }
}
