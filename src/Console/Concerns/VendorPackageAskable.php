<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

trait VendorPackageAskable
{
    protected function getVendorName(): ?string
    {
        return $this->argument('vendor');
    }

    protected function getPackageName(): ?string
    {
        return $this->argument('package');
    }
}
