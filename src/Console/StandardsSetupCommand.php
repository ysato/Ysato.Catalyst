<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Generator;

class StandardsSetupCommand extends Command
{
    use VendorPackageAskableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:standards
                            {vendor=MyVendor : The vendor name (e.g.Acme) in camel case.}
                            {package=MyPackage : The package name (e.g.Blog) in camel case.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up essential development standards and IDE configurations for your project.';

    /**
     * Execute the console command.
     */
    public function handle(Generator $generator)
    {
        $vendor = $this->getVendorNameOrAsk();
        $package = $this->getPackageNameOrAsk();

        $this->components->info('Setting up...');

        $this->components->task('standards', function () use ($vendor, $package, $generator) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($json);
            $json->write($definition);

            $currentIgnore = $generator->getFilesystem()->readFile($this->laravel->basePath('.gitignore'));

            $generator
                ->replacePlaceHolder($vendor, $package)
                ->appendToFile('.gitignore', $currentIgnore)
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
        $definition['scripts']['qa'] = 'phpmd src text ./phpmd.xml';
        $definition['scripts']['tests'] = ['@cs', '@qa', '@test'];

        return $definition;
    }
}