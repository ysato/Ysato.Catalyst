<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait VendorPackageAskableTrait
{
    public function getVendorNameOrAsk()
    {
        return $this->argument('vendor') ?? $this->ask('What is the vendor name ?', 'Acme');
    }

    public function getPackageNameOrAsk()
    {
        return $this->argument('package') ?? $this->ask('What is the package name ?', 'Blog');
    }
}
