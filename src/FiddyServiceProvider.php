<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy;

use Bensondevs\Fiddy\Commands\PresenterMakeCommand;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

final class FiddyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'fiddy');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'fiddy');

        FilamentAsset::register([
            Css::make('fiddy-styles', __DIR__ . '/../resources/css/fiddy.css'),
            Js::make('fiddy-loading-indicators', __DIR__ . '/../resources/js/fiddy-loading-indicators.js'),
        ], package: 'bensondevs/fiddy');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PresenterMakeCommand::class,
            ]);
        }
    }
}
