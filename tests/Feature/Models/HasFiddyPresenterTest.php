<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Forms\Components\FiddySelect;
use Bensondevs\Fiddy\Infolists\Components\FiddyEntry;
use Bensondevs\Fiddy\Tables\Columns\FiddyColumn;
use Bensondevs\Fiddy\Tables\Filters\FiddySelectFilter;
use Bensondevs\Fiddy\Tests\Support\InvalidPresenterAuthor;
use Bensondevs\Fiddy\Tests\Support\MissingPresenterAuthor;
use Bensondevs\Fiddy\Tests\Support\PresentedAuthor;
use Bensondevs\Fiddy\Tests\Support\PresentedPost;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
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

it('resolves select options from the model-declared presenter without presentUsing', function (): void {
    $author = PresentedAuthor::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'avatar_url' => 'https://example.com/bob.jpg',
    ]);

    $post = PresentedPost::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name');

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($label)->toContain('Bob')
        ->and($label)->toContain('Presented: bob@example.com')
        ->and($label)->toContain('https://example.com/bob.jpg');
});

it('resolves column content from the model-declared presenter without presentUsing', function (): void {
    $author = PresentedAuthor::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'avatar_url' => 'https://example.com/bob.jpg',
    ]);

    $post = PresentedPost::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $column = FiddyColumn::make('author')->record($post);

    $html = (string) $column->formatState($column->getRelatedRecord());

    expect($html)->toContain('Bob')
        ->and($html)->toContain('Presented: bob@example.com')
        ->and($html)->toContain('https://example.com/bob.jpg');
});

it('resolves entry content from the model-declared presenter without presentUsing', function (): void {
    $author = PresentedAuthor::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'avatar_url' => 'https://example.com/bob.jpg',
    ]);

    $post = PresentedPost::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $entry = FiddyEntry::make('author')->model($post);

    $html = (string) $entry->formatState($entry->getState());

    expect($html)->toContain('Bob')
        ->and($html)->toContain('Presented: bob@example.com')
        ->and($html)->toContain('https://example.com/bob.jpg');
});

it('builds filter indicator chips from the model-declared presenter option', function (): void {
    $author = PresentedAuthor::query()->create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]);

    $indicator = $author->asFilterIndicator();

    expect($indicator->getTitle())->toBe('Alice')
        ->and($indicator->render())->toContain('Alice')
        ->and($indicator->render())->not->toContain('alice@example.com');

    $filter = FiddySelectFilter::make('author_id')
        ->label('Author')
        ->relationship('author', 'name');

    $method = new ReflectionMethod($filter, 'labelFromRelatedRecord');
    $label = (string) $method->invoke($filter, $author);

    expect($label)->toContain('Alice')
        ->and($label)->not->toContain('alice@example.com');
});

it('throws when the model omits $fiddyPresenter', function (): void {
    $author = MissingPresenterAuthor::query()->create([
        'name' => 'Nope',
    ]);

    expect(fn () => $author->asOption())
        ->toThrow(InvalidArgumentException::class, 'must define protected static string $fiddyPresenter');
});

it('throws when $fiddyPresenter is not a presenter class', function (): void {
    $author = InvalidPresenterAuthor::query()->create([
        'name' => 'Nope',
    ]);

    expect(fn () => $author->asOption())
        ->toThrow(InvalidArgumentException::class, 'must implement');
});
