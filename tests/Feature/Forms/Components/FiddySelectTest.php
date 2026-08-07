<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Forms\Components\FiddySelect\Option;
use Bensondevs\Fiddy\Forms\Components\FiddySelect;
use Bensondevs\Fiddy\Tests\Support\Article;
use Bensondevs\Fiddy\Tests\Support\ArticleOwner;
use Bensondevs\Fiddy\Tests\Support\Author;
use Bensondevs\Fiddy\Tests\Support\AuthorPresenter;
use Bensondevs\Fiddy\Tests\Support\MediaAuthor;
use Bensondevs\Fiddy\Tests\Support\MediaPost;
use Bensondevs\Fiddy\Tests\Support\PlainAuthor;
use Bensondevs\Fiddy\Tests\Support\PlainPost;
use Bensondevs\Fiddy\Tests\Support\Post;
use Bensondevs\Fiddy\Tests\Support\PresentableStatus;
use Bensondevs\Fiddy\Tests\Support\Status;
use Bensondevs\Fiddy\Tests\Support\Widget;
use Bensondevs\Fiddy\Tests\Support\WidgetOwner;
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

it('builds html options from parallel arrays', function (): void {
    $select = FiddySelect::make('assignee')
        ->options([
            1 => 'Alice',
            2 => 'Bob',
        ])
        ->descriptions([
            1 => 'Admin',
            2 => 'Editor',
        ])
        ->hints([
            1 => 'alice@example.com',
        ])
        ->images([
            1 => 'https://example.com/alice.jpg',
        ])
        ->circularImages();

    $options = $select->getOptions();

    expect($options)->toHaveKeys([1, 2])
        ->and($options[1])->toContain('Alice')
        ->and($options[1])->toContain('Admin')
        ->and($options[1])->toContain('alice@example.com')
        ->and($options[1])->toContain('https://example.com/alice.jpg')
        ->and($options[1])->toContain('rounded-full')
        ->and($options[2])->toContain('Bob')
        ->and($options[2])->toContain('Editor');
});

it('aliases defaultOptions to filament options storage', function (): void {
    $select = FiddySelect::make('status')
        ->defaultOptions([
            'draft' => 'Draft',
            'published' => 'Published',
        ]);

    expect($select->getOptions())->toBe([
        'draft' => 'Draft',
        'published' => 'Published',
    ]);
});

it('renders blade icon html without casting Svg to string', function (): void {
    $html = Option::make(1)
        ->title('Alice')
        ->icon('heroicon-o-user')
        ->render();

    expect($html)->toContain('Alice')
        ->and($html)->toContain('<svg');

    $select = FiddySelect::make('assignee')
        ->options([1 => 'Alice'])
        ->icons([1 => 'heroicon-o-user']);

    expect($select->getOptions()[1])->toContain('<svg');
});

it('renders outer and line prefix/suffix icons on separate stacked rows', function (): void {
    $html = Option::make(1)
        ->title('Alice')
        ->description('alice@example.com')
        ->hint('555-0100')
        ->prefixIcon('heroicon-o-user')
        ->suffixIcon('heroicon-o-check-circle')
        ->titlePrefixIcon('heroicon-o-star')
        ->titleSuffixIcon('heroicon-o-flag')
        ->descriptionPrefixIcon('heroicon-o-envelope')
        ->descriptionSuffixIcon('heroicon-o-at-symbol')
        ->hintPrefixIcon('heroicon-o-phone')
        ->hintSuffixIcon('heroicon-o-device-phone-mobile')
        ->render();

    expect($html)->toContain('Alice')
        ->and($html)->toContain('alice@example.com')
        ->and($html)->toContain('555-0100')
        ->and(substr_count($html, '<svg'))->toBeGreaterThanOrEqual(6)
        ->and($html)->toContain('flex w-full min-w-0 overflow-hidden items-center gap-2')
        ->and($html)->toContain('mr-1 flex shrink-0 items-center justify-center')
        ->and($html)->toContain('ml-1 flex shrink-0 items-center justify-center')
        ->and($html)->toContain('mr-1 inline-flex shrink-0 items-center justify-center')
        ->and($html)->toContain('ml-1 inline-flex shrink-0 items-center justify-center')
        ->and($html)->not->toContain('fi-color-')
        ->and($html)->toContain('flex min-w-0 flex-1 flex-col')
        ->and($html)->toContain('flex min-w-0 items-center gap-1 text-xs text-gray-500')
        ->and($html)->toContain('<span class="truncate">alice@example.com</span>')
        ->and($html)->toContain('text-sm font-medium')
        ->and($html)->toContain('text-xs text-gray-500')
        ->and($html)->toContain('text-xs text-gray-400');

    $suffixOnly = Option::make(2)
        ->title('Bob')
        ->suffixIcon('heroicon-o-check')
        ->render();

    expect($suffixOnly)->toContain('Bob')
        ->and($suffixOnly)->toContain('<svg')
        ->and($suffixOnly)->toContain('flex w-full min-w-0 overflow-hidden items-center gap-2')
        ->and($suffixOnly)->toContain('ml-1 flex shrink-0 items-center justify-center')
        ->and($suffixOnly)->not->toContain('fi-color-')
        ->and($suffixOnly)->not->toContain('h-8 w-8'); // suffix-only: no media-sized prefix box

    $withInlineIcons = Option::make(3)
        ->title('Carol')
        ->description('carol@example.com', 'heroicon-o-envelope')
        ->hint('555-0200', 'heroicon-o-phone')
        ->render();

    expect($withInlineIcons)->toContain('carol@example.com')
        ->and($withInlineIcons)->toContain('555-0200')
        ->and(substr_count($withInlineIcons, '<svg'))->toBe(2);

    $select = FiddySelect::make('assignee')
        ->options([1 => 'Alice'])
        ->descriptions([1 => 'Admin'])
        ->hints([1 => 'Hint'])
        ->prefixIcons([1 => 'heroicon-o-user'])
        ->suffixIcons([1 => 'heroicon-o-check-circle'])
        ->descriptionPrefixIcons([1 => 'heroicon-o-envelope'])
        ->hintPrefixIcons([1 => 'heroicon-o-phone']);

    $optionHtml = $select->getOptions()[1];

    expect($optionHtml)->toContain('Alice')
        ->and($optionHtml)->toContain('Admin')
        ->and($optionHtml)->toContain('Hint')
        ->and(substr_count($optionHtml, '<svg'))->toBeGreaterThanOrEqual(4);
});

it('applies fiddy-select class on extra attributes', function (): void {
    $select = FiddySelect::make('supplier_id')
        ->options([1 => 'Acme']);

    expect($select->getExtraAttributes()['class'] ?? null)->toContain('fiddy-select');
});

it('renders prefix and suffix images in block layout by default', function (): void {
    $html = Option::make(1)
        ->title('Alice')
        ->prefixImage('https://example.com/prefix.jpg')
        ->suffixImage('https://example.com/suffix.jpg')
        ->render();

    expect($html)->toContain('https://example.com/prefix.jpg')
        ->and($html)->toContain('https://example.com/suffix.jpg')
        ->and($html)->toContain('flex w-full min-w-0 overflow-hidden items-center gap-2')
        ->and($html)->toContain('mr-1 shrink-0')
        ->and($html)->toContain('ml-1 shrink-0')
        ->and($html)->not->toContain('flex flex-col items-center');

    $select = FiddySelect::make('assignee')
        ->options([1 => 'Alice'])
        ->prefixImages([1 => 'https://example.com/prefix.jpg'])
        ->suffixImages([1 => 'https://example.com/suffix.jpg']);

    expect($select->getOptions()[1])->toContain('https://example.com/prefix.jpg')
        ->and($select->getOptions()[1])->toContain('https://example.com/suffix.jpg');
});

it('keeps searchable preload and multiple fluent on the filament select', function (): void {
    $select = FiddySelect::make('assignees')
        ->multiple()
        ->searchable()
        ->preload();

    expect($select->isMultiple())->toBeTrue()
        ->and($select->isSearchable())->toBeTrue()
        ->and($select->isPreloaded())->toBeTrue()
        ->and($select->isHtmlAllowed())->toBeTrue();
});

it('renders presentOptionUsing labels for relationship records', function (): void {
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

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name')
        ->presentOptionUsing(
            fn (Author $record): Option => Option::make($record->getKey())
                ->title($record->name)
                ->description($record->phone)
                ->hint($record->email)
                ->image($record->avatar_url)
                ->circularImage(),
        );

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($label)->toContain('Alice')
        ->and($label)->toContain('555')
        ->and($label)->toContain('alice@example.com')
        ->and($label)->toContain('https://example.com/alice.jpg')
        ->and($label)->toContain('rounded-full');
});

it('renders presentUsing presenter labels for relationship records', function (): void {
    $author = Author::query()->create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'avatar_url' => 'https://example.com/bob.jpg',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name')
        ->presentUsing(AuthorPresenter::class);

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($label)->toContain('Bob')
        ->and($label)->toContain('Presented: bob@example.com')
        ->and($label)->toContain('https://example.com/bob.jpg');
});

it('auto wires FiddyComponentsPresentable models on relationship', function (): void {
    $author = Author::query()->create([
        'name' => 'Carol',
        'email' => 'carol@example.com',
        'phone' => '999',
        'avatar_url' => 'https://example.com/carol.jpg',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name');

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($label)->toContain('Carol')
        ->and($label)->toContain('carol@example.com')
        ->and($label)->toContain('999')
        ->and($label)->toContain('https://example.com/carol.jpg');
});

it('guesses option fields from model attributes without FiddyComponentsPresentable', function (): void {
    $author = PlainAuthor::query()->create([
        'name' => 'Dave',
        'email' => 'dave@example.com',
        'phone' => '123',
    ]);

    $post = PlainPost::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name');

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($label)->toContain('Dave')
        ->and($label)->toContain('dave@example.com')
        ->and($label)->toContain('123');
});

it('guesses title from the title attribute when name is missing', function (): void {
    Schema::create('articles', function (Blueprint $table): void {
        $table->id();
        $table->string('title');
        $table->string('description')->nullable();
    });

    Schema::create('article_owners', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('article_id')->nullable();
    });

    $article = Article::query()->create([
        'title' => 'Hello World',
        'description' => 'A short blurb',
    ]);

    $owner = ArticleOwner::query()->create([
        'article_id' => $article->getKey(),
    ]);

    $select = FiddySelect::make('article_id')
        ->relationship('article', 'title');

    $select->model($owner);

    $label = $select->getOptionLabelFromRecord($article);

    expect($label)->toContain('Hello World')
        ->and($label)->toContain('A short blurb');
});

it('falls back to title attribute when no guessable attributes exist', function (): void {
    Schema::create('widgets', function (Blueprint $table): void {
        $table->id();
        $table->string('code');
    });

    Schema::create('widget_owners', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('widget_id')->nullable();
    });

    $widget = Widget::query()->create([
        'code' => 'W-100',
    ]);

    $owner = WidgetOwner::query()->create([
        'widget_id' => $widget->getKey(),
    ]);

    $select = FiddySelect::make('widget_id')
        ->relationship('widget', 'code');

    $select->model($owner);

    expect($select->getOptionLabelFromRecord($widget))->toBe('W-100');
});

it('guesses option fields from associative option arrays', function (): void {
    $select = FiddySelect::make('assignee')
        ->options([
            1 => [
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'phone' => '555',
            ],
            2 => [
                'title' => 'Bob',
                'description' => 'Editor',
            ],
        ]);

    $options = $select->getOptions();

    expect($options)->toHaveKeys([1, 2])
        ->and($options[1])->toContain('Alice')
        ->and($options[1])->toContain('alice@example.com')
        ->and($options[1])->toContain('555')
        ->and($options[2])->toContain('Bob')
        ->and($options[2])->toContain('Editor');
});

it('keeps filament option groups when nested arrays are not option attributes', function (): void {
    $select = FiddySelect::make('assignee')
        ->options([
            'Admins' => [
                1 => 'Alice',
                2 => 'Bob',
            ],
        ]);

    expect($select->getOptions())->toBe([
        'Admins' => [
            1 => 'Alice',
            2 => 'Bob',
        ],
    ]);
});

it('guesses image from spatie HasMedia default collection', function (): void {
    $author = MediaAuthor::query()->create([
        'name' => 'Eve',
        'email' => 'eve@example.com',
    ]);

    $post = MediaPost::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name');

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($label)->toContain('Eve')
        ->and($label)->toContain('eve@example.com')
        ->and($label)->toContain('https://cdn.example.com/avatar.jpg');
});

it('applies Option when and unless callbacks', function (): void {
    $enabled = Option::make(1)
        ->title('Alice')
        ->when(true, fn (Option $option) => $option->description('Admin'))
        ->unless(true, fn (Option $option) => $option->hint('hidden'));

    $skipped = Option::make(2)
        ->title('Bob')
        ->when(false, fn (Option $option) => $option->description('Editor'));

    expect($enabled->getDescription())->toBe('Admin')
        ->and($enabled->getHint())->toBeNull()
        ->and($skipped->getDescription())->toBeNull();
});

it('marks Option disabled from bool or closure', function (): void {
    expect(Option::make(1)->title('Alice')->isDisabled())->toBeFalse()
        ->and(Option::make(2)->title('Bob')->disabled()->isDisabled())->toBeTrue()
        ->and(Option::make(3)->title('Carol')->disabled(false)->isDisabled())->toBeFalse()
        ->and(Option::make(4)->title('Dave')->disabled(fn (): bool => true)->isDisabled())->toBeTrue()
        ->and(Option::make(5)->title('Eve')->disabled(fn (): bool => false)->isDisabled())->toBeFalse();
});

it('disables relationship options from presented Option', function (): void {
    $author = Author::query()->create([
        'name' => 'Disabled Author',
        'email' => 'disabled@example.com',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name')
        ->presentOptionUsing(
            fn (Author $record) => Option::make($record->getKey())
                ->title($record->name)
                ->disabled(),
        );

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($label)->toContain('Disabled Author')
        ->and($select->isOptionDisabled($author->getKey(), $label))->toBeTrue()
        ->and($select->hasDisabledOptions())->toBeTrue();
});

it('keeps relationship options enabled when Option is not disabled', function (): void {
    $author = Author::query()->create([
        'name' => 'Enabled Author',
        'email' => 'enabled@example.com',
    ]);

    $post = Post::query()->create([
        'author_id' => $author->getKey(),
        'title' => 'Post',
    ]);

    $select = FiddySelect::make('author_id')
        ->relationship('author', 'name')
        ->presentOptionUsing(
            fn (Author $record) => Option::make($record->getKey())
                ->title($record->name),
        );

    $select->model($post);

    $label = $select->getOptionLabelFromRecord($author);

    expect($select->isOptionDisabled($author->getKey(), $label))->toBeFalse();
});

it('renders tooltip as a native title attribute', function (): void {
    $withTooltip = Option::make(1)
        ->title('Alice')
        ->tooltip('More about Alice')
        ->render();

    $withoutTooltip = Option::make(2)
        ->title('Bob')
        ->render();

    $htmlableTooltip = Option::make(3)
        ->title('Carol')
        ->tooltip(new Illuminate\Support\HtmlString('<strong>Bold tip</strong>'))
        ->render();

    expect($withTooltip)->toContain('title="More about Alice"')
        ->and($withoutTooltip)->not->toContain('title="')
        ->and($htmlableTooltip)->toContain('title="Bold tip"')
        ->and($htmlableTooltip)->not->toContain('<strong>');
});

it('builds rich options from HasLabel and HasIcon enums', function (): void {
    $select = FiddySelect::make('status')
        ->enum(Status::class);

    $options = $select->getOptions();

    expect($options)->toHaveKeys(['draft', 'published'])
        ->and($options['draft'])->toContain('Draft')
        ->and($options['draft'])->toContain('Not visible yet')
        ->and($options['draft'])->toContain('<svg')
        ->and($options['published'])->toContain('Published')
        ->and($options['published'])->toContain('Live on the site')
        ->and($options['published'])->toContain('<svg');
});

it('auto wires FiddyComponentsPresentable enums on static options', function (): void {
    $select = FiddySelect::make('status')
        ->enum(PresentableStatus::class);

    $options = $select->getOptions();

    expect($options)->toHaveKeys(['pending', 'approved'])
        ->and($options['pending'])->toContain('Pending')
        ->and($options['pending'])->toContain('Presented: Pending')
        ->and($options['pending'])->toContain('<svg')
        ->and($options['approved'])->toContain('Approved')
        ->and($options['approved'])->toContain('Presented: Approved')
        ->and($options['approved'])->toContain('<svg');
});
