<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Infolists\Components;

use Bensondevs\Fiddy\Concerns\ResolvesPresentedContent;
use Bensondevs\Fiddy\Models\Contracts\FiddyComponentsPresentable;
use Bensondevs\Fiddy\Support\Content;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class FiddyEntry extends TextEntry
{
    use ResolvesPresentedContent;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->html()
            ->placeholder('-')
            ->getStateUsing(fn (): ?Model => $this->getRelatedRecord())
            ->formatStateUsing(fn (): ?Htmlable => $this->getFormattedRelatedContentHtml());
    }

    protected function contentFromPresentable(FiddyComponentsPresentable $record): Content
    {
        return $record->asEntryContent();
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return $this->resolveRelatedClosureDependencyForEvaluationByName($parameterName)
            ?? parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName);
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        return $this->resolveRelatedClosureDependencyForEvaluationByType($parameterType)
            ?? parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
    }
}
