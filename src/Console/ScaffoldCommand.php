<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Ysato\Catalyst\Scaffold\ContextFactory;
use Ysato\Catalyst\Scaffold\ScaffoldEngineFactory;

use function dirname;
use function in_array;

class ScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
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
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'To automatically generate and configure all necessary components for a new project.';

    /**
     * Execute the console command.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function handle(Filesystem $fs, TemporaryDirectory $sandbox): int
    {
        $caFilepath = $this->getValidatedCaFilePath($fs);

        $vendor = $this->argument('vendor') ?? $this->ask('What is the vendor name ?', 'Acme');
        $package = $this->argument('package') ?? $this->ask('What is the package name ?', 'Blog');
        $php = $this->argument('php') ?? $this->ask('What is the PHP version to use ?', '8.2');
        if (! in_array($php, ['8.2', '8.3', '8.4'], true)) {
            throw new InvalidArgumentException("Invalid PHP version specified. Please use 8.2, 8.3, or 8.4.: [$php]");
        }

        $context = (new ContextFactory())
            ->createFromProject($vendor, $package, $php, $caFilepath, $this->laravel->basePath('.gitignore'));

        $engine = (new ScaffoldEngineFactory(dirname(__DIR__, 2) . '/stubs'))
            ->create($context, $sandbox);

        $this->components->info('Running scaffolding steps');

        $engine->execute();

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
