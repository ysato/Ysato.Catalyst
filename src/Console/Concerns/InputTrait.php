<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

trait InputTrait
{
    protected function getVendorName(): ?string
    {
        return Str::studly($this->argument('vendor'));
    }

    protected function getVendorNameOrAsk(string $question, string $default): string
    {
        $vendor = $this->getVendorName() ?? $this->ask($question, $default);

        return Str::studly($vendor);
    }

    protected function getPackageName(): ?string
    {
        return Str::studly($this->argument('package'));
    }

    protected function getPackageNameOrAsk(string $question, string $default): string
    {
        $package = $this->getPackageName() ?? $this->ask($question, $default);

        return Str::studly($package);
    }

    protected function getValidatedPhpVersion(): string
    {
        $php = $this->argument('php');

        $this->validatePhpVersion($php);

        return $php;
    }

    protected function getValidatedPhpVersionOrAsk(string $question, string $default): string
    {
        $php = $this->argument('php') ?? $this->ask($question, $default);

        $this->validatePhpVersion($php);

        return $php;
    }

    private function validatePhpVersion(string $php): void
    {
        if (! in_array($php, ['8.2', '8.3', '8.4'], true)) {
            throw new InvalidArgumentException("Invalid PHP version specified. Please use 8.2, 8.3, or 8.4.: [$php]");
        }
    }

    protected function hasOptionStrict(string $key): bool
    {
        return $this->input->hasParameterOption("--$key") && $this->hasOption($key);
    }

    protected function getValidatedCaFilePath(Filesystem $fs): ?string
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
