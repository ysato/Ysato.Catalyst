<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Ysato\Catalyst\Input;
use Ysato\Catalyst\Steps\GenerateGitignore;
use Ysato\Catalyst\Steps\PrepareSandboxFromStubs;
use Ysato\Catalyst\Steps\ReplacePlaceholders;
use Ysato\Catalyst\Steps\ScaffoldComposerManifest;
use Ysato\Catalyst\Steps\ScaffoldDocker;

class NewProjectScaffoldingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:scaffold
                            {vendor? : The vendor name in pascal case (e.g.Acme).}
                            {package? : The package name in pascal case (e.g.Blog).}
                            {php? : Specify the PHP version for the project (e.g., 8.2).}
                            {--with-ca-file= : Path to a custom CA certificate to trust within the container (e.g, certs/certificate.pem).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To automatically generate and configure all necessary components for a new project.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $fs, TemporaryDirectory $sandbox, Finder $finder): int
    {
        $caFilepath = $this->getValidatedCaFilePath($fs);

        $rawVendor = $this->argument('vendor') ?? $this->ask('What is the vendor name ?', 'Acme');
        $vendor = Str::studly($rawVendor);

        $rawPackage = $this->argument('package') ?? $this->ask('What is the package name ?', 'Blog');
        $package = Str::studly($rawPackage);

        $php = $this->argument('php') ?? $this->ask('What is the PHP version to use ?', '8.2');
        if (! in_array($php, ['8.2', '8.3', '8.4'], true)) {
            throw new InvalidArgumentException("Invalid PHP version specified. Please use 8.2, 8.3, or 8.4.: [$php]");
        }

        $input = new Input($vendor, $package, $php, $caFilepath);

        $sandbox
            ->deleteWhenDestroyed()
            ->create();

        $steps = [
            new PrepareSandboxFromStubs($fs, $sandbox, __DIR__ . '/../stubs'),
            new GenerateGitignore($fs, $sandbox, $this->laravel),
            new ScaffoldComposerManifest($fs, $sandbox),
            new ScaffoldDocker($fs, $sandbox, $input),
            new ReplacePlaceholders($fs, $sandbox, $finder, $input),
        ];

        $this->components->info('Running scaffolding steps');

        (new Collection($steps))
            ->each(fn($step) => $this->components->task($step::class, $step->execute()))
            ->whenNotEmpty(fn() => $this->newLine());

        $fs->mirror($sandbox->path(), $this->laravel->basePath(), options: ['override' => true]);

        $sandbox->delete();

        return 0;
    }

    private function hasOptionStrict(string $key): bool
    {
        return $this->input->hasParameterOption("--$key") && $this->hasOption($key);
    }

    private function getValidatedCaFilePath(Filesystem $fs): ?string
    {
        if (! $this->hasOptionStrict('with-ca-file')) {
            return null;
        }

        $caFilepath = $this->option('with-ca-file');
        if ($caFilepath === null || $caFilepath === '') {
            throw new InvalidArgumentException('CA file path is required. (e.g., --with-ca-file=certs/certificate.pem).');
        }

        if (! $fs->exists($caFilepath)) {
            throw new InvalidArgumentException("The specified CA file does not exist: [$caFilepath]");
        }

        return $caFilepath;
    }
}
