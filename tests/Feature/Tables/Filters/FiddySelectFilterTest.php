<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Forms\Components\FiddySelect;
use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Tables\Filters\FiddySelectFilter;
use Bensondevs\Fiddy\Tables\Filters\Indicator;
use Bensondevs\Fiddy\Tests\Support\Author;
use Bensondevs\Fiddy\Tests\Support\AuthorPresenter;
use Bensondevs\Fiddy\Tests\Support\Post;
use Bensondevs\Fiddy\Tests\Support\PresentableStatus;
use Bensondevs\Fiddy\Tests\Support\Status;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;

beforeEach(function (): void {
    Schema::create('authors', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('avatar_url')->nullable();
    });

    Schema::create('posts', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('author_id')->nullable();
        $table->string('title')->nullable();
    });
});

it('builds a fiddy select form field for relationship filters', function (): void {
    $author = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'phone' => '555',
        'avatar_url' => 'https://example.com/alice.jpg',
    ]);

    $filter = FiddySelectFilter::make('author_id')
        ->relationship('author', 'name');

    $field = $filter->getFormField();

    expect($field)->toBeInstanceOf(FiddySelect::class)
        ->and($field->isHtmlAllowed())->toBeTrue()
        ->and($field->isNative())->toBeFalse();

    $label = $field->getOptionLabelFromRecord($author);

    expect($label)->toContain('Alice')
        ->and($label)->toContain('alice@example.com')
        ->and($label)->toContain('555')
        ->and($label)->toContain('https://example.com/alice.jpg');
});

it('uses presentUsing presenter for relationship option labels', function (): void {
    $author = Author::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'avatar_url' => 'https://example.com/bob.jpg',
    ]);

    $filter = FiddySelectFilter::make('author_id')
        ->relationship('author', 'name')
        ->presentUsing(AuthorPresenter::class);

    $label = $filter->getFormField()->getOptionLabelFromRecord($author);

    expect($label)->toContain('Bob')
        ->and($label)->toContain('Presented: bob@example.com')
        ->and($label)->toContain('https://example.com/bob.jpg');
});

it('uses presentOptionUsing closure for relationship option labels', function (): void {
    $author = Author::query()->create([
        'name' => 'Carol',
        'email' => 'carol@example.com',
    ]);

    $filter = FiddySelectFilter::make('author_id')
        ->relationship('author', 'name')
        ->presentOptionUsing(
            fn (Author $record): Option => Option::make($record->getKey())
                ->title('Custom ' . $record->name)
                ->description($record->email),
        );

    $label = $filter->getFormField()->getOptionLabelFromRecord($author);

    expect($label)->toContain('Custom Carol')
        ->and($label)->toContain('carol@example.com');
});

it('builds rich html for static options with descriptions', function (): void {
    $filter = FiddySelectFilter::make('status')
        ->options([
            1 => 'Alice',
            2 => 'Bob',
        ])
        ->descriptions([
            1 => 'Admin',
            2 => 'Editor',
        ]);

    $options = $filter->getFormField()->getOptions();

    expect($options)->toHaveKeys([1, 2])
        ->and($options[1])->toContain('Alice')
        ->and($options[1])->toContain('Admin')
        ->and($options[2])->toContain('Bob')
        ->and($options[2])->toContain('Editor');
});

it('applies a non-relationship select filter to the query', function (): void {
    $alice = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]);

    $bob = Author::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
    ]);

    Post::query()->create([
        'author_id' => $alice->getKey(),
        'title' => 'A',
    ]);

    Post::query()->create([
        'author_id' => $bob->getKey(),
        'title' => 'B',
    ]);

    $filter = FiddySelectFilter::make('author_id')
        ->options([
            $alice->getKey() => 'Alice',
            $bob->getKey() => 'Bob',
        ]);

    $results = Post::query()
        ->tap(fn ($query) => $filter->apply($query, ['value' => (string) $alice->getKey()]))
        ->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->author_id)->toBe($alice->getKey());
});

it('uses asFilterIndicator title for relationship filter chips', function (): void {
    $author = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'phone' => '555',
    ]);

    $indicator = $author->asFilterIndicator();

    expect($indicator->getTitle())->toBe('Alice')
        ->and($indicator->render())->toContain('Alice')
        ->and($indicator->render())->not->toContain('alice@example.com')
        ->and($indicator->render())->not->toContain('555');

    $filter = FiddySelectFilter::make('author_id')
        ->label('Author')
        ->relationship('author', 'name');

    $method = new ReflectionMethod($filter, 'labelFromRelatedRecord');
    $label = (string) $method->invoke($filter, $author);

    expect($label)->toContain('Alice')
        ->and($label)->not->toContain('alice@example.com')
        ->and($label)->not->toContain('555');
});

it('renders indicator prefix icons without casting Svg to string', function (): void {
    $html = Indicator::make('Acme')
        ->prefixIcon(Heroicon::OutlinedBuildingOffice)
        ->render();

    expect($html)->toContain('Acme')
        ->and($html)->toContain('<svg')
        ->and($html)->toContain('leading-none')
        ->and($html)->toContain('[&_svg]:block');
});

it('builds rich form options from HasLabel and HasIcon enums', function (): void {
    $filter = FiddySelectFilter::make('status')
        ->enum(Status::class);

    $options = $filter->getFormField()->getOptions();

    expect($options)->toHaveKeys(['draft', 'published'])
        ->and($options['draft'])->toContain('Draft')
        ->and($options['draft'])->toContain('Not visible yet')
        ->and($options['draft'])->toContain('<svg')
        ->and($options['published'])->toContain('Published')
        ->and($options['published'])->toContain('<svg');
});

it('uses HasLabel and HasIcon for enum filter chips', function (): void {
    $filter = FiddySelectFilter::make('status')
        ->label('Status')
        ->enum(Status::class);

    $method = new ReflectionMethod($filter, 'resolveStaticFilterIndicatorLabel');
    $label = (string) $method->invoke($filter, 'draft');

    expect($label)->toContain('Draft')
        ->and($label)->toContain('<svg')
        ->and($label)->not->toContain('Not visible yet');
});

it('uses asFilterIndicator for FiddyComponentsPresentable enum chips', function (): void {
    $filter = FiddySelectFilter::make('status')
        ->label('Status')
        ->enum(PresentableStatus::class);

    $options = $filter->getFormField()->getOptions();

    expect($options['pending'])->toContain('Presented: Pending');

    $method = new ReflectionMethod($filter, 'resolveStaticFilterIndicatorLabel');
    $label = (string) $method->invoke($filter, 'pending');

    expect($label)->toContain('Chip: Pending')
        ->and($label)->toContain('<svg')
        ->and($label)->not->toContain('Presented: Pending');
});
