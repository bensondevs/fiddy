<?php

declare(strict_types=1);

namespace Bensondevs\Fiddy\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'fiddy:presenter')]
final class PresenterMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'fiddy:presenter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Fiddy presenter class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Presenter';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/presenter.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : dirname(__DIR__, 2) . $stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Presenters\Fiddy';
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInput(): string
    {
        $name = parent::getNameInput();

        $name = Str::of($name)
            ->replace('/', '\\')
            ->explode('\\')
            ->map(fn (string $segment): string => Str::studly($segment))
            ->implode('\\');

        foreach (['OptionPresenter', 'ContentPresenter', 'Presenter'] as $suffix) {
            if (Str::endsWith($name, $suffix) && $suffix !== 'Presenter') {
                $name = Str::beforeLast($name, $suffix);

                break;
            }
        }

        if (! Str::endsWith($name, 'Presenter')) {
            $name .= 'Presenter';
        }

        return $name;
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array<int, mixed>>
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the presenter already exists'],
        ];
    }
}
