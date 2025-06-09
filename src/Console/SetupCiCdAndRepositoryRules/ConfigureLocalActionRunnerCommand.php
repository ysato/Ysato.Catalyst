<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\SetupCiCdAndRepositoryRules;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskable;
use Ysato\Catalyst\Generator;

class ConfigureLocalActionRunnerCommand extends Command
{
    use VendorPackageAskable;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup-ci-cd-and-repository-rules:configure-local-action-runner
                            {vendor : The vendor name (e.g.Acme) in camel case.}
                            {package : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure Local Action Runner';

    /**
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $vendor = $this->getVendorName();
        $package = $this->getPackageName();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($generator, $vendor, $package) {
            $search = ['__Vendor__', '__Package__'];
            $replace = [Str::snake($vendor), Str::snake($package)];

            $generator
                ->replacePlaceHolder($search, $replace)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }
}
