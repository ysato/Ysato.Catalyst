<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ScaffoldCoreStructure;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskable;

class GenerateComposerMetadataCommand extends Command
{
    use VendorPackageAskable;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:scaffold-core-structure:generate-composer-metadata
                            {vendor : The vendor name (e.g.Acme) in camel case.}
                            {package : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Composer Metadata';

    protected $hidden = true;

    public function handle()
    {
        $vendor = $this->argument('vendor');
        $package = $this->argument('package');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($vendor, $package) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition(Str::kebab($vendor), Str::kebab($package), $json);
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
        $definition['name'] = sprintf('%s/%s', $vendor, $package);
        $definition['license'] = 'proprietary';
        $definition['config']['platform']['php'] = '8.2';

        return $definition;
    }
}
