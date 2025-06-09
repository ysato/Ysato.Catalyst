<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskableTrait;
use Ysato\Catalyst\Console\Concerns\Washable;
use Ysato\Catalyst\Generator;

class ActSetupCommand extends Command
{
    use VendorPackageAskableTrait;
    use Washable;

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
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        $this->components->info('Setting up...');

        $this->components->task('act', function () use ($generator, $vendor, $package) {
            $current = $generator->fs->readFile($this->laravel->basePath('.gitignore'));
            $washed = $this->wash($current);

            $contents = <<< 'EOF'
.actrc
/certs/*
!/certs/.gitkeep

EOF;

            $generator
                ->replacePlaceHolder(Str::snake($vendor), Str::snake($package))
                ->dumpFile('.gitignore', $washed)
                ->appendToFile('.gitignore', $contents)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    protected function wash(string $contents): string
    {
        return preg_replace(
            ['#^.actrc$\R?#m', '#^!?/certs(/?|/.*)$\R?#m'],
            ['', ''],
            $contents
        );
    }
}
