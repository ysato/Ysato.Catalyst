<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskableTrait;
use Ysato\Catalyst\Generator;

class ActSetupCommand extends Command
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:act
                            {vendor? : The vendor name (e.g.Acme) in camel case.}
                            {package? : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configures local execution for GitHub Actions.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $vendor = Str::snake($this->getVendorNameOrAsk());
        $package = Str::snake($this->getPackageNameOrAsk());

        $this->components->info('Setting up...');

        $this->components->task('act', function () use ($generator, $vendor, $package) {
            $currentIgnore = $generator->fs->readFile($this->laravel->basePath('.gitignore'));

            $generator
                ->replacePlaceHolder($vendor, $package)
                ->dumpFile('.gitignore', $currentIgnore)
                ->appendToFile('.gitignore', ".actrc\n")
                ->generate($this->laravel->basePath());
        });

        return 0;
    }
}
