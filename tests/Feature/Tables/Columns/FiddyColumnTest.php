<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Tables\Columns\FiddyColumn;
use Bensondevs\Fiddy\Tests\Support\Author;
use Bensondevs\Fiddy\Tests\Support\AuthorPresenter;
use Bensondevs\Fiddy\Tests\Support\PlainAuthor;
use Bensondevs\Fiddy\Tests\Support\PlainPost;
use Bensondevs\Fiddy\Tests\Support\Post;
use Bensondevs\Fiddy\Tests\Support\PresentableStatus;
use Bensondevs\Fiddy\Tests\Support\Status;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

it('renders Content HTML for belongsTo FiddyComponentsPresentable models', function (): void {
    $author = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'phone' => '555',
        'avatar_url' => 'https://example.com/alice.jpg',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Hello',
    ]);

    $column = FiddyColumn::make('author')->record($post);

    $html = (string) $column->formatState($column->getRelatedRecord());

    expect($html)->toContain('Alice')
        ->and($html)->toContain('alice@example.com')
        ->and($html)->not->toContain('555');
});

it('guesses Content fields for non-FiddyComponentsPresentable belongsTo models', function (): void {
    $author = PlainAuthor::query()->create([
        'name' => 'Dave',
        'email' => 'dave@example.com',
        'phone' => '123',
    ]);

    $post = PlainPost::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $column = FiddyColumn::make('author')->record($post);

    $html = (string) $column->formatState($column->getRelatedRecord());

    expect($html)->toContain('Dave')
        ->and($html)->toContain('dave@example.com')
        ->and($html)->toContain('123');
});

it('uses presentUsing content presenter for the related model', function (): void {
    $author = Author::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'avatar_url' => 'https://example.com/bob.jpg',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $column = FiddyColumn::make('author')
        ->presentUsing(AuthorPresenter::class)
        ->record($post);

    $html = (string) $column->formatState($column->getRelatedRecord());

    expect($html)->toContain('Bob')
        ->and($html)->toContain('Presented: bob@example.com')
        ->and($html)->toContain('https://example.com/bob.jpg');
});

it('uses presentContentUsing closure for the related model', function (): void {
    $author = Author::query()->create([
        'name' => 'Carol',
        'email' => 'carol@example.com',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $column = FiddyColumn::make('author')
        ->presentContentUsing(
            fn (Author $record): Content => Content::make()
                ->title('Custom ' . $record->name, 'name')
                ->description($record->email, 'email'),
        )
        ->record($post);

    $html = (string) $column->formatState($column->getRelatedRecord());

    expect($html)->toContain('Custom Carol')
        ->and($html)->toContain('carol@example.com');
});

it('shows placeholder when the relation is missing', function (): void {
    $post = Post::query()->create([
        'author_id' => null,
        'title' => 'Orphan',
    ]);

    $column = FiddyColumn::make('author')->record($post);

    expect($column->getRelatedRecord())->toBeNull()
        ->and($column->getPlaceholder())->toBe('-');
});

it('allows overriding the related record explicitly', function (): void {
    $author = Author::query()->create([
        'name' => 'Eve',
        'email' => 'eve@example.com',
    ]);

    $post = Post::query()->create([
        'author_id' => null,
        'title' => 'Orphan',
    ]);

    $column = FiddyColumn::make('author')
        ->relatedRecord($author)
        ->record($post);

    $html = (string) $column->formatState($column->getRelatedRecord());

    expect($html)->toContain('Eve')
        ->and($html)->toContain('eve@example.com');
});

it('injects the related model into url closures by type', function (): void {
    $author = Author::query()->create([
        'name' => 'Frank',
        'email' => 'frank@example.com',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $column = FiddyColumn::make('author')
        ->url(fn (Author $related): string => 'https://example.com/authors/' . $related->getKey())
        ->record($post);

    expect($column->getUrl())->toBe('https://example.com/authors/' . $author->getKey());
});

it('resolves a different related record per table row', function (): void {
    $alice = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]);

    $bob = Author::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
    ]);

    $postA = Post::query()->create([
        'author_id' => $alice->getKey(),
        'title' => 'A',
    ]);

    $postB = Post::query()->create([
        'author_id' => $bob->getKey(),
        'title' => 'B',
    ]);

    $column = FiddyColumn::make('author');

    $htmlA = (string) $column->record($postA)->formatState($column->getRelatedRecord());
    $htmlB = (string) $column->record($postB)->formatState($column->getRelatedRecord());

    expect($htmlA)->toContain('Alice')
        ->and($htmlA)->not->toContain('Bob')
        ->and($htmlB)->toContain('Bob')
        ->and($htmlB)->not->toContain('Alice');
});

it('constrains the query when searchable matches related author name', function (): void {
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

    $column = FiddyColumn::make('author')->searchable();
    $isFirst = true;

    $results = Post::query()
        ->tap(fn ($query) => $column->applySearchConstraint($query, 'Alice', $isFirst))
        ->with('author')
        ->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->author->name)->toBe('Alice');
});

it('orders by related author name when sortable', function (): void {
    $bob = Author::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
    ]);

    $alice = Author::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]);

    Post::query()->create([
        'author_id' => $bob->getKey(),
        'title' => 'B',
    ]);

    Post::query()->create([
        'author_id' => $alice->getKey(),
        'title' => 'A',
    ]);

    $column = FiddyColumn::make('author')->sortable();

    $names = Post::query()
        ->tap(fn ($query) => $column->applySort($query, 'asc'))
        ->with('author')
        ->get()
        ->pluck('author.name')
        ->all();

    expect($names)->toBe(['Alice', 'Bob']);
});

it('marks the column individually searchable when filterable', function (): void {
    $column = FiddyColumn::make('author')->filterable();

    expect($column->isSearchable())->toBeTrue()
        ->and($column->isIndividuallySearchable())->toBeTrue();
});

it('eager loads the belongsTo relationship', function (): void {
    $author = Author::query()->create([
        'name' => 'Grace',
        'email' => 'grace@example.com',
    ]);

    Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $column = FiddyColumn::make('author');

    $query = Post::query();
    $column->applyEagerLoading($query);

    expect($query->getEagerLoads())->toHaveKey('author');
});

it('builds Content from HasLabel and HasIcon enums', function (): void {
    $html = Content::fromEnum(Status::Draft)->render();

    expect($html)->toContain('Draft')
        ->and($html)->toContain('Not visible yet')
        ->and($html)->toContain('<svg');
});

it('uses asColumnContent for FiddyComponentsPresentable enums', function (): void {
    $html = PresentableStatus::Pending->asColumnContent()->render();

    expect($html)->toContain('Pending')
        ->and($html)->toContain('Column: Pending')
        ->and($html)->toContain('<svg')
        ->and($html)->not->toContain('Presented: Pending');
});
