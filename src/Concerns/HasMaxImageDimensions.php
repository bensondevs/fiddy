<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Concerns;

use Bensondevs\Fiddy\Support\ImageDimensions;

trait HasMaxImageDimensions
{
    protected int | string | null $maxImageWidth = null;

    protected int | string | null $maxImageHeight = null;

    public function maxImageWidth(int | string | null $width): static
    {
        $this->maxImageWidth = $width;

        return $this;
    }

    public function maxImageHeight(int | string | null $height): static
    {
        $this->maxImageHeight = $height;

        return $this;
    }

    public function getMaxImageWidth(): ?string
    {
        return ImageDimensions::normalize($this->maxImageWidth) ?? $this->defaultMaxImageWidth();
    }

    public function getMaxImageHeight(): ?string
    {
        return ImageDimensions::normalize($this->maxImageHeight) ?? $this->defaultMaxImageHeight();
    }

    protected function defaultMaxImageWidth(): ?string
    {
        return '8rem';
    }

    protected function defaultMaxImageHeight(): ?string
    {
        return '8rem';
    }
}
