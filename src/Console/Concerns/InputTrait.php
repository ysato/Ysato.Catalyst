<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait InputTrait
{
    protected function getVendorName(): ?string
    {
        return $this->argument('vendor');
    }

    protected function getPackageName(): ?string
    {
        return $this->argument('package');
    }

    protected function getPhpVersion(): ?string
    {
        return $this->argument('php');
    }

    protected function getCaFilePath(): ?string
    {
        return $this->option('with-ca-file');
    }

    protected function hasOptionStrict(string $key): bool
    {
        $nullOrEmpty = $this->option($key) === null || $this->option($key) === '';

        return ! ($this->input->hasParameterOption("--$key") && $nullOrEmpty);
    }
}
