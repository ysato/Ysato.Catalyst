<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ConfigureStaticAnalysis;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskable;
use Ysato\Catalyst\Console\Concerns\Washable;
use Ysato\Catalyst\Generator;

class SetupPHPCodeSnifferCommand extends Command
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

        $this->task(function () use ($vendor, $package, $generator) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($json);
            $json->write($definition);

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

    /**
     * @return array<string, mixed>
     * @throws ParsingException
     */
    private function getNewDefinition(JsonFile $json): array
    {
        $definition = $json->read();

        if (! array_key_exists('scripts', $definition)) {
            $definition['scripts'] = [];
        }

        $definition['scripts']['cs'] = 'phpcs';
        $definition['scripts']['cs-fix'] = 'phpcbf';

        if (! array_key_exists('tests', $definition['scripts'])) {
            $definition['scripts']['tests'] = [];
        }

        if (! in_array('@cs', $definition['scripts']['tests'], true)) {
            $definition['scripts']['tests'][] = '@cs';
        }

        return $definition;
    }

    protected function wash(string $contents): string
    {
        return preg_replace('#^/?.php_cs.cache$\R?#m', '', $contents);
    }
}
