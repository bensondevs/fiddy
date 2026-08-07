<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Tables\Columns\FiddyTimestampColumn;
use Bensondevs\Fiddy\Tests\Support\Author;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-08-07 15:30:00'));

    Schema::create('authors', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('avatar_url')->nullable();
    });

    Schema::create('notes', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function makeNote(): Model
{
    $note = new class extends Model
    {
        protected $table = 'notes';

        protected $guarded = [];
    };

    $note->save();

    return $note->fresh();
}

it('formats datetime state as HTML by default', function (): void {
    $note = makeNote();

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->record($note)
        ->formatState($note->created_at);

    expect($html)->toContain('Aug')
        ->and($html)->toContain('2026');
});

it('describes relative difference with a calendar icon path', function (): void {
    $note = makeNote();
    $note->forceFill(['created_at' => Carbon::parse('2026-08-10 12:00:00')])->save();

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeDiffForHuman()
        ->record($note->fresh())
        ->formatState($note->fresh()->created_at);

    expect($html)->toContain('from now')
        ->and($html)->toContain('<svg');
});

it('describes same-day relative difference in hours not zero seconds', function (): void {
    $note = makeNote();
    $note->forceFill(['created_at' => Carbon::parse('2026-08-07 12:56:00')])->save();

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeDiffForHuman()
        ->record($note->fresh())
        ->formatState($note->fresh()->created_at);

    expect($html)->toContain('ago')
        ->and($html)->not->toContain('0 seconds')
        ->and($html)->toContain('hour');
});

it('describes the subject name with a default user icon', function (): void {
    $note = makeNote();
    $author = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]);

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeSubject()
        ->getSubjectUsing(fn (): Author => $author)
        ->record($note)
        ->formatState($note->created_at);

    expect($html)->toContain('Alice')
        ->and($html)->toContain('<svg');
});

it('uses getSubjectNameUsing for the subject label', function (): void {
    $note = makeNote();
    $author = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]);

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeSubject()
        ->getSubjectUsing(fn (): Author => $author)
        ->getSubjectNameUsing(fn (Author $subject): string => 'By ' . $subject->name)
        ->record($note)
        ->formatState($note->created_at);

    expect($html)->toContain('By Alice');
});

it('falls back to the system label when no subject is resolved', function (): void {
    $note = makeNote();

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeSubject()
        ->record($note)
        ->formatState($note->created_at);

    expect($html)->toContain('System');
});

it('renders the subject photo when enabled', function (): void {
    $note = makeNote();
    $author = Author::query()->create([
        'name' => 'Alice',
        'avatar_url' => 'https://example.com/alice.jpg',
    ]);

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeSubject()
        ->getSubjectUsing(fn (): Author => $author)
        ->subjectPhoto()
        ->getSubjectPhotoUsing(fn (Author $subject): string => $subject->avatar_url)
        ->record($note)
        ->formatState($note->created_at);

    expect($html)->toContain('https://example.com/alice.jpg')
        ->and($html)->toContain('rounded-full');
});

it('places description above the title when position is top', function (): void {
    $note = makeNote();
    $author = Author::query()->create(['name' => 'Alice']);

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeSubject('top')
        ->getSubjectUsing(fn (): Author => $author)
        ->record($note)
        ->formatState($note->created_at);

    $alicePos = strpos($html, 'Alice');
    $augPos = strpos($html, 'Aug');

    expect($alicePos)->not->toBeFalse()
        ->and($augPos)->not->toBeFalse()
        ->and($alicePos)->toBeLessThan($augPos);
});

it('stacks subject then diff when both describe methods use bottom', function (): void {
    $note = makeNote();
    $note->forceFill(['created_at' => Carbon::parse('2026-08-04 12:00:00')])->save();
    $author = Author::query()->create(['name' => 'Alice']);

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->describeSubject()
        ->describeDiffForHuman()
        ->getSubjectUsing(fn (): Author => $author)
        ->record($note->fresh())
        ->formatState($note->fresh()->created_at);

    $alicePos = strpos($html, 'Alice');
    $agoPos = strpos($html, 'ago');

    expect($alicePos)->not->toBeFalse()
        ->and($agoPos)->not->toBeFalse()
        ->and($alicePos)->toBeLessThan($agoPos);
});

it('keeps explicit description icon overrides', function (): void {
    $note = makeNote();

    $html = (string) FiddyTimestampColumn::make('created_at')
        ->descriptionPrefixIcon(Heroicon::OutlinedCalendar)
        ->describeDiffForHuman()
        ->record($note)
        ->formatState($note->created_at);

    expect($html)->toContain('<svg');
});
