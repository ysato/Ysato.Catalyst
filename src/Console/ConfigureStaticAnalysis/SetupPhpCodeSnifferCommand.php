<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ConfigureStaticAnalysis;

use Illuminate\Console\Command;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskable;
use Ysato\Catalyst\Console\Concerns\Washable;
use Ysato\Catalyst\Generator;

class SetupPhpCodeSnifferCommand extends Command
{
    use VendorPackageAskable;
    use Washable;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:configure-static-analysis:setup-php-code-sniffer
                            {vendor : The vendor name (e.g.Acme) in camel case.}
                            {package : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup PHPCS';

    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $vendor = $this->getVendorName();
        $package = $this->getPackageName();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($vendor, $package, $generator) {
            $search = ['__Vendor__', '__Package__'];
            $replace = [$vendor, $package];

            $ignore = $generator->fs->readFile($this->laravel->basePath('.gitignore'));
            $washed = $this->wash($ignore);

            $generator
                ->replacePlaceHolder($search, $replace)
                ->dumpFile('.gitignore', $washed)
                ->appendToFile('.gitignore', "/.php_cs.cache\n")
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    protected function wash(string $contents): string
    {
        return preg_replace('#^/?.php_cs.cache$\R?#m', '', $contents);
    }
}
