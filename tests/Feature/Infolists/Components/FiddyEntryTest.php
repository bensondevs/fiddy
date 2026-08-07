<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Infolists\Components\FiddyEntry;
use Bensondevs\Fiddy\Support\Content;
use Bensondevs\Fiddy\Tests\Support\Author;
use Bensondevs\Fiddy\Tests\Support\AuthorPresenter;
use Bensondevs\Fiddy\Tests\Support\PlainAuthor;
use Bensondevs\Fiddy\Tests\Support\PlainPost;
use Bensondevs\Fiddy\Tests\Support\Post;
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

it('renders FiddyComponentsPresentable relationship HTML for belongsTo', function (): void {
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

    $entry = FiddyEntry::make('author')->model($post);

    $html = (string) $entry->formatState($entry->getState());

    expect($html)->toContain('Alice')
        ->and($html)->toContain('alice@example.com')
        ->and($html)->toContain('555')
        ->and($html)->toContain('https://example.com/alice.jpg');
});

it('guesses content fields for non-FiddyComponentsPresentable belongsTo models', function (): void {
    $author = PlainAuthor::query()->create([
        'name' => 'Dave',
        'email' => 'dave@example.com',
        'phone' => '123',
    ]);

    $post = PlainPost::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $entry = FiddyEntry::make('author')->model($post);

    $html = (string) $entry->formatState($entry->getState());

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

    $entry = FiddyEntry::make('author')
        ->presentUsing(AuthorPresenter::class)
        ->model($post);

    $html = (string) $entry->formatState($entry->getState());

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

    $entry = FiddyEntry::make('author')
        ->presentContentUsing(
            fn (Author $record): Content => Content::make()
                ->title('Custom ' . $record->name)
                ->description($record->email),
        )
        ->model($post);

    $html = (string) $entry->formatState($entry->getState());

    expect($html)->toContain('Custom Carol')
        ->and($html)->toContain('carol@example.com');
});

it('shows placeholder when the relation is missing', function (): void {
    $post = Post::query()->create([
        'author_id' => null,
        'title' => 'Orphan',
    ]);

    $entry = FiddyEntry::make('author')->model($post);

    expect($entry->getState())->toBeNull()
        ->and($entry->getPlaceholder())->toBe('-');
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

    $entry = FiddyEntry::make('author')
        ->relatedRecord($author)
        ->model($post);

    $html = (string) $entry->formatState($entry->getState());

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

    $entry = FiddyEntry::make('author')
        ->url(fn (Author $related): string => 'https://example.com/authors/' . $related->getKey())
        ->model($post);

    expect($entry->getUrl())->toBe('https://example.com/authors/' . $author->getKey());
});
