---
name: fiddy-development
description: >-
  Build Filament UI with Fiddy rich Option/Content presentation — presenters,
  FiddyComponentsPresentable on models/enums, attribute guessing, FiddyColumn,
  FiddySelect, filters, and timestamp components.
---

# Fiddy development

## When to use this skill

Use when working with `FiddySelect`, `FiddySelectFilter`, `FiddyColumn`, `FiddyEntry`, `FiddyTimestampColumn` / `FiddyTimestampEntry`, `FiddyNumericInput`, `FiddyImageEntry`, presenters, `HasFiddyPresenter`, `FiddyComponentsPresentable`, or when choosing how to present related models / enums.

There is **no** `FiddyDateColumn` — use Filament date columns or `FiddyTimestampColumn` for datetime + subject/diff HTML.

## Resolution order

Highest priority first (Select/Filter options use the Option path; Column/Entry use the Content path):

1. Inline closure — `presentOptionUsing` / `presentContentUsing`
2. `->presentUsing(Presenter::class)` — `toOption()` / `toContent()`
3. Model or enum implements `FiddyComponentsPresentable`
4. Attribute guessing (`OptionGuesser` / `Content::guess`) or Filament enum contracts

## On the model (no presenter)

Implement `FiddyComponentsPresentable`. Fiddy detects it automatically — do not require `presentUsing`.

| Method | Surface |
| --- | --- |
| `asOption()` | `FiddySelect` + filter **dropdown** options |
| `asColumnContent()` | `FiddyColumn` |
| `asEntryContent()` | `FiddyEntry` |
| `asFilterIndicator()` | Active `FiddySelectFilter` **chips** only |

```php
class Author implements FiddyComponentsPresentable
{
    public function asOption(): Option { /* ... */ }
    public function asColumnContent(): Content { /* ... */ }
    public function asEntryContent(): Content { /* ... */ }
    public function asFilterIndicator(): Indicator
    {
        return Indicator::make($this->name);
    }
}
```

Wire with relationship only:

```php
FiddySelect::make('author_id')->relationship('author', 'name');
FiddyColumn::make('author');
FiddyEntry::make('author');
FiddySelectFilter::make('author_id')->relationship('author', 'name');
```

## No presenter, no contract

Guessed attributes: title `name|title`, description `email|description`, hint `phone|hint`, image `avatar_url` or Spatie Media Library default collection.

Enums without the contract: Filament `HasLabel` / `HasIcon` / `HasDescription` via `->enum(Status::class)`.

## Separate presenter

```bash
php artisan fiddy:presenter User
```

`Presenter` requires `toOption(): Option` and `toContent(): Content`. Constructor takes a `Model`.

Prefer declaring it once on the model with `HasFiddyPresenter` (implements the four presentable methods by delegating):

```php
class User extends Model implements FiddyComponentsPresentable
{
    use HasFiddyPresenter;

    protected static string $fiddyPresenter = UserPresenter::class;
}
```

```php
FiddySelect::make('user_id')->relationship('user', 'name');
FiddyColumn::make('user');
FiddyEntry::make('user');
FiddySelectFilter::make('user_id')->relationship('user', 'name');
```

Chips: trait default uses `toOption()` title (+ prefix icon). Override `asFilterIndicator()` when needed.

Or wire per component:

```php
FiddySelect::make('user_id')->relationship('user', 'name')->presentUsing(UserPresenter::class);
FiddyColumn::make('user')->presentUsing(UserPresenter::class);
FiddyEntry::make('user')->presentUsing(UserPresenter::class);
FiddySelectFilter::make('user_id')->relationship('user', 'name')->presentUsing(UserPresenter::class);
```

Caveats:

- Enums cannot use `presentUsing` / `HasFiddyPresenter` (Model-only). Use presentable or Filament contracts.
- Component-level `presentUsing(...)` alone does **not** define filter chips. Chips use `asFilterIndicator()` on presentable models (including `HasFiddyPresenter`); else relationship title attribute.
- `presentUsing(...)` overrides a presentable model on the same component.

---

## FiddySelect / FiddySelectFilter (Option)

### Presentation methods

| Method | Input | Behavior |
| --- | --- | --- |
| `presentOptionUsing(?Closure)` | Closure → `Option` | Highest priority |
| `presentUsing(?string)` | `PresentsOption` class-string | `new $class($record)->toOption()` |
| *(fallback)* | Presentable / guess / enum contracts | `asOption()` or `OptionGuesser::from` |

Filter `presentUsing` / static option helpers apply to the nested select **dropdown**. Active chips do **not** use the presenter.

### `presentOptionUsing` closure injections

Related/option model (the record being presented as an option):

| How | Parameter | Value |
| --- | --- | --- |
| By name | `$record` | Related model |
| By name | `$related` | Same related model |
| By type | `Model` / concrete class | Related model |

Must return an `Option` instance.

### Static option helpers (Select + Filter)

Values are `array|Arrayable|Closure|null` keyed like `options()`:

- Text: `descriptions`, `hints`
- Media: `images` / `prefixImages`, `suffixImages`, `circularImages(bool|Closure)`
- Icons: `icons` / `prefixIcons`, `suffixIcons`; `titleIcons` / `titlePrefixIcons` / `titleSuffixIcons`; same for `description*` and `hint*`

Also: `->enum(Status::class)`, `->relationship(...)`, Filament searchable/preload as usual.

### `Option` fluent API

`Option::make($value)` — used in closures, `toOption()`, `asOption()`.

Icons: `string|BackedEnum|Htmlable|null`.

| Method | Notes |
| --- | --- |
| `value($value)` | Option value / key |
| `title(?string)` | Primary label |
| `description(?string, $icon = null)` | Optional 2nd arg sets description icon |
| `hint(?string, $icon = null)` | Optional 2nd arg sets hint icon |
| `image` / `prefixImage` / `suffixImage` | URLs |
| `circularImage(bool = true)` | Round media |
| `disabled(bool\|Closure = true)` | Disable option |
| `tooltip(string\|Htmlable\|null)` | Hover tooltip (plain text extracted from Htmlable) |
| `icon` (= `prefixIcon`), `prefixIcon` / `suffixIcon` | Cell icons |
| `titleIcon` / `titlePrefixIcon` / `titleSuffixIcon` | Beside title |
| `descriptionIcon` / `descriptionPrefixIcon` / `descriptionSuffixIcon` | Beside description |
| `hintIcon` / `hintPrefixIcon` / `hintSuffixIcon` | Beside hint |

No `aboveTitle` / `aboveDescription` on `Option` (those exist only on `Content`).

### Filter chips (`Indicator`)

`Indicator::make(?string $title)` with `title`, `icon` / `prefixIcon` / `suffixIcon`.

Presentable models: `asFilterIndicator()`. Enums: `Indicator::fromEnum` → presentable or `HasLabel`/`HasIcon`. Relationship without presentable: plain title attribute string.

---

## FiddyColumn / FiddyEntry (Content)

`FiddyColumn` / `FiddyEntry` render a **related** BelongsTo / HasOne as `Content` HTML. Missing relation → placeholder `-` (column). Entry uses `asEntryContent()` when presentable; Column uses `asColumnContent()`.

### Related record

1. `relatedRecord(Model|Closure|null)` — Closure → `?Model` (Filament injections; table/infolist row is `record`)
2. Else BelongsTo/HasOne named like the component
3. Else `data_get($row, $name)` when value is a `Model`

### Presentation

| Method | Input |
| --- | --- |
| `presentContentUsing(?Closure)` | Must return `Content` |
| `presentUsing(?string)` | `PresentsContent` class-string |
| *(fallback)* | `asColumnContent()` / `asEntryContent()` or `Content::guess()` |

`presentContentUsing` injections (**related** model, not the row):

- By name: `record`, `related`
- By type: `Model`, concrete related class

### Display helpers (Column)

| Method | Notes |
| --- | --- |
| `circularImages(bool\|Closure = true)` | Forces circular media after resolve |
| `searchable(...)` | Default related `LIKE` on `name`, `title`, `email`, `description` |
| `sortable(...)` | Default order by related `name` |
| `filterable()` | `searchable(condition: true, isIndividual: true)` |

BelongsTo/HasOne named like the column are eager-loaded. `Content` title/description `$attribute` is metadata only — does not change which DB columns searchable/sortable query.

### `Content` fluent API

`Content::make()` for columns, entries, `toContent()`, `asColumnContent()`, `asEntryContent()`. Icons: `string|BackedEnum|Htmlable|null`.

**Text**

| Method | Arguments |
| --- | --- |
| `title(?string $title, ?string $attribute = null)` | Optional attribute metadata |
| `description(?string $description, ?string $attribute = null)` | Same |
| `hint(?string $hint)` | Tertiary line |
| `aboveTitle(?string)` / `aboveDescription(?string)` | Lines above title (timestamps) |

**Media:** `image` / `prefixImage` / `suffixImage`, `circularImage(bool = true)`

**Icons:** `icon` (= prefix), `prefixIcon` / `suffixIcon`; `titleIcon` / `titlePrefixIcon` / `titleSuffixIcon`; same pattern for `description*`, `hint*`, `aboveTitle*`, `aboveDescription*`

Helpers: `Content::guess(Model)`, `Content::fromEnum(UnitEnum)`, `Content::resolveImageUrl(Model)` (`avatar_url`, then Spatie default collection).

Unlike `Option`, `description` / `hint` do **not** take an icon as a second positional argument — use `descriptionIcon(...)` / `hintIcon(...)`.

---

## FiddyTimestampColumn / FiddyTimestampEntry

Shared `FormatsTimestampContent`. Title = translated datetime (`M j, Y g:i A` in component timezone).

| Method | Closure / input |
| --- | --- |
| `describeSubject($position = 'bottom')` | `'bottom'` or `'top'`/`'above'`; default user icon |
| `describeDiffForHuman($position = 'bottom')` | Relative diff (full datetime precision); calendar icon by default |
| `getSubjectUsing(?Closure)` | → `?Model`; injects `record` (row). Else soft activity-log causer |
| `getSubjectNameUsing(?Closure)` | → name; injects `record`, `subject` |
| `subjectPhoto(bool\|Closure = true)` | Show circular subject photo |
| `getSubjectPhotoUsing(?Closure)` | → `?string` URL; injects `subject`, `record`. Default `Content::resolveImageUrl` |
| `titlePrefixIcon` / `titleSuffixIcon` | On datetime title |
| `descriptionPrefixIcon` / `descriptionSuffixIcon` | On subject/diff lines; explicit prefix disables default overwrite |

Layout: bottom subject → description, bottom diff → hint; top subject → aboveTitle, top diff → aboveDescription.

```php
FiddyTimestampColumn::make('created_at')
    ->describeSubject()
    ->getSubjectUsing(fn (Model $record) => $record->creator)
    ->subjectPhoto()
    ->describeDiffForHuman()
    ->titlePrefixIcon(Heroicon::OutlinedClock);
```

---

## Other components (brief)

- `FiddyNumericInput`: thousand-separator mask; `money(currency:)`, `spellAmount(locale?:)`
- `FiddyImageEntry`: Filament image entry + `rounded()`, click-to-preview (`previewable(false)` to disable; `url()` wins over preview)
