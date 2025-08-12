<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Ysato\Catalyst\Scaffold\Input;
use Ysato\Catalyst\Scaffold\Scaffolder;

use function assert;
use function dirname;
use function file_exists;
use function in_array;
use function is_string;

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
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function handle(): int
    {
        $caFilepath = $this->getValidatedCaFilePath();

        $vendor = $this->argument('vendor') ?? $this->ask('What is the vendor name ?', 'Acme');
        assert(is_string($vendor), 'Vendor must be string');

        $package = $this->argument('package') ?? $this->ask('What is the package name ?', 'Blog');
        assert(is_string($package), 'Package must be string');

        $php = $this->argument('php') ?? $this->ask('What is the PHP version to use ?', '8.2');
        assert(is_string($php), 'PHP version must be string');

        if (! in_array($php, ['8.2', '8.3', '8.4'], true)) {
            throw new InvalidArgumentException("Invalid PHP version specified. Please use 8.2, 8.3, or 8.4.: [$php]");
        }

        $input = new Input($vendor, $package, $php, $caFilepath);

        $scaffolder = Scaffolder::create($this->laravel->basePath(), dirname(__DIR__, 2) . '/stubs');

        $scaffolder->scaffold($input);

        $this->components->info("Project '{$vendor}/{$package}' scaffolded successfully");

        return 0;
    }

    private function hasOptionStrict(string $key): bool
    {
        return $this->input->hasParameterOption("--$key") && $this->hasOption($key);
    }

    private function getValidatedCaFilePath(): string|null
    {
        if (! $this->hasOptionStrict('with-ca-file')) {
            return null;
        }

        $caFilepath = $this->option('with-ca-file');
        assert(is_string($caFilepath) || $caFilepath === null, 'CA filepath must be string or null');

        if ($caFilepath === null || $caFilepath === '') {
            throw new InvalidArgumentException(
                'CA file path is required. (e.g., --with-ca-file=certs/certificate.pem).',
            );
        }

        if (! file_exists($caFilepath)) {
            throw new InvalidArgumentException("The specified CA file does not exist: [$caFilepath]");
        }

        return $caFilepath;
    }
}
