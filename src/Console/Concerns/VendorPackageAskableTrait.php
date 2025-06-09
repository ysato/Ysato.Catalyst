<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait VendorPackageAskableTrait
{
    public function getVendorName()
    {
        return $this->argument('vendor');
    }

    public function getPackageName(): ?string
    {
        return $this->argument('package');
    }

    public function getVendorNameOrAsk()
    {
        return $this->getVendorName() ?? $this->ask('What is the vendor name ?', 'Acme');
    }

    public function getPackageNameOrAsk()
    {
        return $this->getPackageName() ?? $this->ask('What is the package name ?', 'Blog');
    }
}
