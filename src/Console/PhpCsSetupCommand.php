<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskableTrait;
use Ysato\Catalyst\Generator;

class PhpCsSetupCommand extends Command
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:phpcs
                            {vendor? : The vendor name (e.g.Acme) in camel case.}
                            {package? : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes PHP Code Sniffer by setting up configuration files and recommended coding standards for the project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        $this->components->info('Setting up...');

        $this->components->task('phpcs', function () use ($vendor, $package, $generator) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($json);
            $json->write($definition);

            $currentIgnore = $generator->fs->readFile($this->laravel->basePath('.gitignore'));

            $generator
                ->replacePlaceHolder($vendor, $package)
                ->dumpFile('.gitignore', $currentIgnore)
                ->appendToFile('.gitignore', ".php_cs.cache\n")
                ->generate($this->laravel->basePath());
        });

        return 0;
    }

    /**
     * @return array<string, mixed>
     * @throws ParsingException
     */
    private function getNewDefinition(JsonFile $json): array
    {
        $definition = $json->read();
        $definition['scripts']['cs'] = 'phpcs';
        $definition['scripts']['cs-fix'] = 'phpcbf';

        $tests = Arr::get($definition, 'scripts.tests', []);

        $hasTest = Arr::has($definition, 'scripts.test');
        if ($hasTest && ! in_array('@test', $tests, true)) {
            $definition['scripts']['tests'][] = '@test';
        }

        if (! in_array('@cs', $tests, true)) {
            $definition['scripts']['tests'][] = '@cs';
        }

        return $definition;
    }
}
