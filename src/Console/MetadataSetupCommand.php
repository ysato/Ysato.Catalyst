<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Seld\JsonLint\ParsingException;

class MetadataSetupCommand extends Command
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:metadata
                            {vendor? : The vendor name (e.g.Acme) in camel case.}
                            {package? : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Configures the project's metadata.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        $this->components->info('Setting up...');

        $this->components->task('metadata', function () use ($vendor, $package) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($vendor, $package, $json);
            $json->write($definition);
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
        unset(
            $definition['keywords'],
            $definition['homepage'],
            $definition['description'],
        );
        $definition['name'] = sprintf('%s/%s', Str::kebab($vendor), Str::kebab($package));
        $definition['license'] = 'proprietary';
        $definition['config']['platform']['php'] = '8.2';

        return $definition;
    }
}