<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Filesystem\Filesystem;
use Ysato\Catalyst\Console\Concerns\InputTrait;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;

class NewProjectScaffoldingCommand extends Command
{
    use InputTrait;
    use TaskRenderable;

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
    public function handle(Filesystem $fs)
    {
        $caFilepath = $this->getValidatedCaFilePath($fs);

        $vendor = $this->getVendorNameOrAsk('What is the vendor name ?', 'Acme');
        $package = $this->getPackageNameOrAsk('What is the package name ?', 'Blog');
        $php = $this->getValidatedPhpVersionOrAsk('What is the PHP version to use ?', '8.2');

        $workflow = [
            'catalyst:scaffold-core-structure:generate-gitignore',
            'catalyst:scaffold-core-structure:scaffold-composer-manifest',
            'catalyst:scaffold-core-structure:scaffold-architecture-layers',
            'catalyst:scaffold-core-structure:define-containerized-environment',
            'catalyst:configure-static-analysis:setup-php-code-sniffer',
            'catalyst:configure-static-analysis:setup-php-mess-detector',
            'catalyst:configure-static-analysis:setup-openapi-linter',
            'catalyst:setup-ci-cd-and-repository-rules:generate-github-actions-workflows',
            'catalyst:setup-ci-cd-and-repository-rules:setup-repository-rulesets',
            'catalyst:setup-ci-cd-and-repository-rules:configure-local-action-runner',
            'catalyst:setup-local-development-environment:initialize-ide-settings',
            'catalyst:generate-developer-shortcuts:generate-justfile',
        ];

        foreach ($workflow as $command) {
            match (Str::between($command, ':', ':')) {
                'scaffold-core-structure' => $this->runScaffoldCoreStructure($command, $vendor, $package, $php, $caFilepath),
                'configure-static-analysis' => $this->runConfigureStaticAnalysis($command, $vendor, $package),
                'setup-ci-cd-and-repository-rules' => $this->runSetupCiCdAndRepositoryRules($command, $vendor, $package, $php),
                'setup-local-development-environment' => $this->runSetupLocalDevelopmentEnvironment($command),
                'generate-developer-shortcuts' => $this->runGenerateDeveloperShortcuts($command, $php),
            };
        }

        return 0;
    }

    private function runScaffoldCoreStructure(
        string $command,
        string $vendor,
        string $package,
        string $php,
        ?string $caFilepath
    ): void {
        $step = Str::afterLast($command, ':');

        match ($step) {
            'generate-gitignore' => $this->call($command),
            'scaffold-composer-manifest' => $this->call($command, compact('vendor', 'package', 'php')),
            'scaffold-architecture-layers' => $this->call($command, compact('vendor', 'package')),
            'define-containerized-environment' => $this->call($command, array_filter([
                'php' => $php,
                '--with-ca-file' => $caFilepath,
            ], fn($value) => $value !== null)),
        };
    }

    private function runConfigureStaticAnalysis(string $command, string $vendor, string $package): void
    {
        $step = Str::afterLast($command, ':');

        match ($step) {
            'setup-php-code-sniffer' => $this->call($command, compact('vendor', 'package')),
            'setup-php-mess-detector' => $this->call($command),
            'setup-openapi-linter' => $this->call($command),
        };
    }

    private function runSetupCiCdAndRepositoryRules(string $command, string $vendor, string $package, string $php): void
    {
        $step = Str::afterLast($command, ':');

        match ($step) {
            'generate-github-actions-workflows' => $this->call($command, compact('php')),
            'setup-repository-rulesets' => $this->call($command, compact('php')),
            'configure-local-action-runner' => $this->call($command, compact('vendor', 'package')),
        };
    }

    private function runSetupLocalDevelopmentEnvironment(string $command): void
    {
        $step = Str::afterLast($command, ':');

        match ($step) {
            'initialize-ide-settings' => $this->call($command),
        };
    }

    private function runGenerateDeveloperShortcuts(string $command, string $php): void
    {
        $step = Str::afterLast($command, ':');

        match ($step) {
            'generate-justfile' => $this->call($command, compact('php')),
        };
    }
}
