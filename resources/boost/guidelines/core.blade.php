## Fiddy

Fiddy adds rich Image, Title, Description, hint, and icon presentation on top of Filament forms, infolists, tables, and filters. Display data flows through shared `Option` (select / filter dropdowns) and `Content` (columns / entries) DTOs.

### Main components

- Forms: `FiddySelect`, `FiddyNumericInput`
- Infolists: `FiddyEntry`, `FiddyImageEntry`, `FiddyTimestampEntry`
- Tables: `FiddyColumn`, `FiddyTimestampColumn`
- Filters: `FiddySelectFilter` (dropdowns reuse select presentation; active chips use `Indicator`)

### Presentation

Fill `Option` / `Content` in one of three ways (highest priority first):

1. Inline closures (`presentOptionUsing` / `presentContentUsing`)
2. `->presentUsing(SomePresenter::class)` — separate `Presenter` with `toOption()` / `toContent()`
3. Model or enum implements `FiddyComponentsPresentable` (`asOption`, `asColumnContent`, `asEntryContent`, `asFilterIndicator`) — no `presentUsing` needed
4. Attribute guessing (`name|title`, `email|description`, `phone|hint`, Spatie media / `avatar_url`) or Filament enum contracts (`HasLabel` / `HasIcon` / `HasDescription`)

Generate a presenter with `php artisan fiddy:presenter {Model}`.

For detailed patterns when building or refactoring Fiddy UI, activate the `fiddy-development` skill.

@verbatim
<code-snippet name="Relationship select with presenter" lang="php">
use Bensondevs\Fiddy\Forms\Components\FiddySelect;

FiddySelect::make('user_id')
    ->relationship('user', 'name')
    ->presentUsing(UserPresenter::class);
</code-snippet>
@endverbatim
