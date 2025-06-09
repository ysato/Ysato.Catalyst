<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskableTrait;
use Ysato\Catalyst\Generator;

class ArchitectureSrcSetupCommand extends Command
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:architecture-src
                            {vendor? : The vendor name (e.g.Acme) in camel case.}
                            {package? : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the base architecture for a Laravel project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator): int
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        $this->components->info('Setting up...');

        $this->components->task('architecture-src', function () use ($vendor, $package, $generator) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($vendor, $package, $json);
            $json->write($definition);

            $search = ['__Vendor__', '__Package__'];
            $replace = [$vendor, $package];

            $generator
                ->replacePlaceHolder($search, $replace)
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    /**
     * @return array<string, mixed>
     * @throws ParsingException
     */
    private function getNewDefinition(string $vendor, string $package, JsonFile $json): array
    {
        $definition = $json->read();
        $definition['autoload']['psr-4']["$vendor\\$package\\"] = 'src/';

        return $definition;
    }
}
