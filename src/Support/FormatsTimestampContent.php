<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Support;

use BackedEnum;
use Closure;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

trait FormatsTimestampContent
{
    use ResolvesDateDifferenceHint;

    protected bool $describeSubject = false;

    protected bool $describeDiffForHuman = false;

    protected string $subjectPosition = 'bottom';

    protected string $diffPosition = 'bottom';

    protected ?Closure $getSubjectUsing = null;

    protected ?Closure $getSubjectNameUsing = null;

    protected bool | Closure $showSubjectPhoto = false;

    protected ?Closure $getSubjectPhotoUsing = null;

    protected string | BackedEnum | Htmlable | null $titlePrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $titleSuffixIcon = null;

    protected string | BackedEnum | Htmlable | null $descriptionPrefixIcon = null;

    protected string | BackedEnum | Htmlable | null $descriptionSuffixIcon = null;

    protected bool $descriptionPrefixIconExplicit = false;

    public function describeSubject(string $position = 'bottom'): static
    {
        $this->describeSubject = true;
        $this->subjectPosition = $this->normalizePosition($position);

        if (! $this->descriptionPrefixIconExplicit && blank($this->descriptionPrefixIcon)) {
            $this->descriptionPrefixIcon = Heroicon::OutlinedUser;
        }

        return $this;
    }

    public function describeDiffForHuman(string $position = 'bottom'): static
    {
        $this->describeDiffForHuman = true;
        $this->diffPosition = $this->normalizePosition($position);

        return $this;
    }

    public function getSubjectUsing(?Closure $callback): static
    {
        $this->getSubjectUsing = $callback;

        return $this;
    }

    public function getSubjectNameUsing(?Closure $callback): static
    {
        $this->getSubjectNameUsing = $callback;

        return $this;
    }

    public function subjectPhoto(bool | Closure $condition = true): static
    {
        $this->showSubjectPhoto = $condition;

        return $this;
    }

    public function getSubjectPhotoUsing(?Closure $callback): static
    {
        $this->getSubjectPhotoUsing = $callback;

        return $this;
    }

    public function titlePrefixIcon(string | BackedEnum | Htmlable | null $icon): static
    {
        $this->titlePrefixIcon = $icon;

        return $this;
    }

    public function titleSuffixIcon(string | BackedEnum | Htmlable | null $icon): static
    {
        $this->titleSuffixIcon = $icon;

        return $this;
    }

    public function descriptionPrefixIcon(string | BackedEnum | Htmlable | null $icon): static
    {
        $this->descriptionPrefixIcon = $icon;
        $this->descriptionPrefixIconExplicit = true;

        return $this;
    }

    public function descriptionSuffixIcon(string | BackedEnum | Htmlable | null $icon): static
    {
        $this->descriptionSuffixIcon = $icon;

        return $this;
    }

    public function toTimestampHtml(mixed $state): ?Htmlable
    {
        $title = $this->formatTimestampTitle($state);

        if (blank($title) && ! $this->describeSubject && ! $this->describeDiffForHuman) {
            return null;
        }

        $record = $this->getRecord();
        $subject = $record instanceof Model ? $this->resolveSubject($record) : null;
        $subjectName = $this->describeSubject ? $this->resolveSubjectName($record, $subject) : null;
        $diff = $this->describeDiffForHuman
            ? $this->resolveDifferenceHint($state, $this->getTimezone(), startOfDay: false)
            : null;

        $content = Content::make();

        if (filled($title)) {
            $content->title($title);
        }

        if (filled($this->titlePrefixIcon)) {
            $content->titlePrefixIcon($this->titlePrefixIcon);
        }

        if (filled($this->titleSuffixIcon)) {
            $content->titleSuffixIcon($this->titleSuffixIcon);
        }

        $this->applyDescribeLines($content, $subjectName, $diff);

        if ($this->shouldShowSubjectPhoto() && $subject instanceof Model) {
            $photo = $this->resolveSubjectPhoto($subject);

            if (filled($photo)) {
                $content
                    ->prefixImage($photo)
                    ->circularImage();
            }
        }

        return new HtmlString($content->render());
    }

    protected function formatTimestampTitle(mixed $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        return Carbon::parse($state)
            ->setTimezone($this->getTimezone())
            ->translatedFormat('M j, Y g:i A');
    }

    protected function applyDescribeLines(Content $content, ?string $subjectName, ?string $diff): void
    {
        $top = [];
        $bottom = [];

        if ($this->describeSubject && filled($subjectName)) {
            $entry = [
                'text' => $subjectName,
                'prefixIcon' => $this->descriptionPrefixIcon ?? Heroicon::OutlinedUser,
                'suffixIcon' => $this->descriptionSuffixIcon,
            ];

            if ($this->subjectPosition === 'top') {
                $top[] = $entry;
            } else {
                $bottom[] = $entry;
            }
        }

        if ($this->describeDiffForHuman && filled($diff)) {
            $diffIcon = Heroicon::OutlinedCalendar;

            if (! $this->describeSubject && $this->descriptionPrefixIconExplicit) {
                $diffIcon = $this->descriptionPrefixIcon;
            }

            $entry = [
                'text' => $diff,
                'prefixIcon' => $diffIcon,
                'suffixIcon' => null,
            ];

            if ($this->diffPosition === 'top') {
                $top[] = $entry;
            } else {
                $bottom[] = $entry;
            }
        }

        if (isset($top[0])) {
            $content
                ->aboveTitle($top[0]['text'])
                ->aboveTitlePrefixIcon($top[0]['prefixIcon'])
                ->aboveTitleSuffixIcon($top[0]['suffixIcon'] ?? null);
        }

        if (isset($top[1])) {
            $content
                ->aboveDescription($top[1]['text'])
                ->aboveDescriptionPrefixIcon($top[1]['prefixIcon'])
                ->aboveDescriptionSuffixIcon($top[1]['suffixIcon'] ?? null);
        }

        if (isset($bottom[0])) {
            $content
                ->description($bottom[0]['text'])
                ->descriptionPrefixIcon($bottom[0]['prefixIcon'])
                ->descriptionSuffixIcon($bottom[0]['suffixIcon'] ?? null);
        }

        if (isset($bottom[1])) {
            $content
                ->hint($bottom[1]['text'])
                ->hintPrefixIcon($bottom[1]['prefixIcon'])
                ->hintSuffixIcon($bottom[1]['suffixIcon'] ?? null);
        }
    }

    protected function resolveSubject(?Model $record): ?Model
    {
        if (! $record instanceof Model) {
            return null;
        }

        if ($this->getSubjectUsing !== null) {
            $subject = $this->evaluate($this->getSubjectUsing, [
                'record' => $record,
            ]);

            return $subject instanceof Model ? $subject : null;
        }

        return ResolvesActivitySubject::forTimestampColumn($record, $this->getName());
    }

    protected function resolveSubjectName(?Model $record, ?Model $subject): string
    {
        if ($this->getSubjectNameUsing !== null) {
            $name = $this->evaluate($this->getSubjectNameUsing, [
                'record' => $record,
                'subject' => $subject,
            ]);

            if (filled($name)) {
                return (string) $name;
            }
        }

        return ResolvesActivitySubject::name($subject) ?? __('fiddy::timestamp.system');
    }

    protected function resolveSubjectPhoto(Model $subject): ?string
    {
        if ($this->getSubjectPhotoUsing !== null) {
            $photo = $this->evaluate($this->getSubjectPhotoUsing, [
                'subject' => $subject,
                'record' => $this->getRecord(),
            ]);

            return filled($photo) ? (string) $photo : null;
        }

        return Content::resolveImageUrl($subject);
    }

    protected function shouldShowSubjectPhoto(): bool
    {
        return (bool) $this->evaluate($this->showSubjectPhoto);
    }

    protected function normalizePosition(string $position): string
    {
        return match ($position) {
            'top', 'above' => 'top',
            default => 'bottom',
        };
    }
}
