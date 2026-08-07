<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Concerns;

use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Support\Contracts\PresentsContent;
use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;

trait ResolvesPresentedContent
{
    use ResolvesRelatedRecord;

    protected ?Closure $presentContentUsing = null;

    /**
     * @var class-string<PresentsContent>|null
     */
    protected ?string $contentPresenterClass = null;

    public function presentContentUsing(?Closure $callback): static
    {
        $this->presentContentUsing = $callback;

        return $this;
    }

    /**
     * @param  class-string<PresentsContent>|null  $presenter
     */
    public function presentUsing(?string $presenter): static
    {
        if (filled($presenter) && ! is_a($presenter, PresentsContent::class, allow_string: true)) {
            throw new InvalidArgumentException(
                "[{$presenter}] must implement " . PresentsContent::class . '.',
            );
        }

        $this->contentPresenterClass = $presenter;

        return $this;
    }

    protected function getFormattedRelatedContentHtml(): ?Htmlable
    {
        $related = $this->getRelatedRecord();

        if (! $related instanceof Model) {
            return null;
        }

        $content = $this->resolvePresentedContent($related);

        if (! $content instanceof Content) {
            return null;
        }

        return new HtmlString($this->applyContentDefaults($content)->render());
    }

    protected function resolvePresentedContent(Model $record): ?Content
    {
        if (filled($this->presentContentUsing)) {
            /** @var mixed $content */
            $content = $this->evaluate(
                $this->presentContentUsing,
                namedInjections: [
                    'record' => $record,
                    'related' => $record,
                ],
                typedInjections: [
                    Model::class => $record,
                    $record::class => $record,
                ],
            );

            return $content instanceof Content ? $content : null;
        }

        if (filled($this->contentPresenterClass)) {
            /** @var class-string<PresentsContent> $presenterClass */
            $presenterClass = $this->contentPresenterClass;
            $presenter = new $presenterClass($record);

            return $presenter->toContent();
        }

        if ($record instanceof FiddyComponentsPresentable) {
            return $this->contentFromPresentable($record);
        }

        return Content::guess($record);
    }

    protected function contentFromPresentable(FiddyComponentsPresentable $record): Content
    {
        return $record->asColumnContent();
    }

    protected function applyContentDefaults(Content $content): Content
    {
        if ($this->evaluate($this->circularImages)) {
            $content->circularImage();
        }

        return $content;
    }
}
