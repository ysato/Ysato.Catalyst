<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait VendorPackageAskableTrait
{
    private function getVendorName()
    {
        return $this->argument('vendor');
    }

    private function getPackageName(): ?string
    {
        return $this->argument('package');
    }

    private function getVendorNameOrAsk()
    {
        return $this->getVendorName() ?? $this->ask('What is the vendor name ?', 'Acme');
    }

    private function getPackageNameOrAsk()
    {
        return $this->getPackageName() ?? $this->ask('What is the package name ?', 'Blog');
    }
}
