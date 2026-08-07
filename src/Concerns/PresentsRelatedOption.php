<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Concerns;

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\OptionGuesser;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

trait PresentsRelatedOption
{
    use ResolvesPresentedOption;
    use ResolvesRelatedRecord;

    protected function getFormattedRelatedOptionHtml(): ?Htmlable
    {
        $related = $this->getRelatedRecord();

        if (! $related instanceof Model) {
            return null;
        }

        $option = $this->resolvePresentedOption($related);

        if (! $option instanceof Option) {
            $title = $this->fallbackTitleFromRelated($related);

            if (blank($title)) {
                return null;
            }

            $option = Option::make($related->getKey())->title($title);
        }

        return new HtmlString($this->applyRelatedOptionDefaults($option)->render());
    }

    protected function fallbackTitleFromRelated(Model $related): ?string
    {
        foreach (OptionGuesser::TITLE_ATTRIBUTES as $attribute) {
            $value = data_get($related, $attribute);

            if (filled($value)) {
                return is_string($value) ? $value : (string) $value;
            }
        }

        return null;
    }

    protected function applyRelatedOptionDefaults(Option $option): Option
    {
        if ($this->evaluate($this->circularImages)) {
            $option->circularImage();
        }

        return $option;
    }
}
