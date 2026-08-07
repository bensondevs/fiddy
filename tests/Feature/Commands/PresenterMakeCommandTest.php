<?php

declare(strict_types=1);

use Bensondevs\Fiddy\Support\Presenter;
use Illuminate\Support\Facades\File;

afterEach(function (): void {
    $paths = [
        app_path('Presenters/Fiddy/UserPresenter.php'),
        app_path('Presenters/Fiddy/AuthorPresenter.php'),
        app_path('Presenters/Fiddy/AuthorPresenterPresenter.php'),
    ];

    foreach ($paths as $path) {
        if (File::exists($path)) {
            File::delete($path);
        }
    }
});

it('creates a unified presenter with toOption and toContent', function (): void {
    $path = app_path('Presenters/Fiddy/UserPresenter.php');

    $this->artisan('fiddy:presenter', ['name' => 'User'])
        ->assertSuccessful();

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);

    expect($contents)
        ->toContain('namespace App\\Presenters\\Fiddy;')
        ->toContain('final class UserPresenter extends Presenter')
        ->toContain('public function toOption(): Option')
        ->toContain('public function toContent(): Content')
        ->toContain('use Bensondevs\\Fiddy\\Support\\Presenter;');

    require_once $path;

    expect(is_subclass_of(App\Presenters\Fiddy\UserPresenter::class, Presenter::class))->toBeTrue();
});

it('does not double the Presenter suffix', function (): void {
    $this->artisan('fiddy:presenter', ['name' => 'AuthorPresenter'])
        ->assertSuccessful();

    expect(File::exists(app_path('Presenters/Fiddy/AuthorPresenter.php')))->toBeTrue()
        ->and(File::exists(app_path('Presenters/Fiddy/AuthorPresenterPresenter.php')))->toBeFalse();
});

it('normalizes OptionPresenter and ContentPresenter names to Presenter', function (): void {
    $this->artisan('fiddy:presenter', ['name' => 'AuthorOptionPresenter'])
        ->assertSuccessful();

    expect(File::exists(app_path('Presenters/Fiddy/AuthorPresenter.php')))->toBeTrue()
        ->and(File::get(app_path('Presenters/Fiddy/AuthorPresenter.php')))
        ->toContain('final class AuthorPresenter extends Presenter');
});
