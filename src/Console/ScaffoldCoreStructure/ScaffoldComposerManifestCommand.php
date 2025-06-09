<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\ScaffoldCoreStructure;

use Composer\Factory;
use Composer\Json\JsonFile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Seld\JsonLint\ParsingException;
use Ysato\Catalyst\Console\Concerns\PhpVersionAskable;
use Ysato\Catalyst\Console\Concerns\TaskRenderable;
use Ysato\Catalyst\Console\Concerns\VendorPackageAskable;

class ScaffoldComposerManifestCommand extends Command
{
    use VendorPackageAskable;
    use PhpVersionAskable;
    use TaskRenderable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:scaffold-core-structure:scaffold-composer-manifest
                            {vendor : The vendor name (e.g.Acme) in camel case.}
                            {package : The package name (e.g.Blog) in camel case.}
                            {php : Specify the PHP version for the project (e.g., 8.2).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold Composer Manifest';

    protected $hidden = true;

    public function handle()
    {
        $vendor = $this->getVendorName();
        $package = $this->getPackageName();
        $php = $this->getPhpVersion();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->task(function () use ($vendor, $package, $php) {
            $json = new JsonFile(Factory::getComposerFile());
            $definition = $this->getNewDefinition($json, $vendor, $package, $php);
            $json->write($definition);
        });

        return 0;
    }

    /**
     * @return array<string, mixed>
     * @throws ParsingException
     */
    private function getNewDefinition(JsonFile $json, string $vendor, string $package, string $php): array
    {
        $definition = $json->read();

        unset(
            $definition['keywords'],
            $definition['homepage'],
            $definition['description'],
        );

        $newDefinition = [
            'name' => sprintf('%s/%s', Str::kebab($vendor), Str::kebab($package)),
            'license' => 'proprietary',
            'autoload' => [
                'psr-4' => [
                    "$vendor\\$package\\" => 'src/',
                ],
            ],
            'scripts' => [
                'test' => [
                    '@php artisan config:clear --ansi',
                    '@php artisan test',
                ],
                'coverage' => [
                    "@php artisan config:clear --ansi",
                    "@php -d zend_extension=xdebug.so -d xdebug.mode=coverage artisan test --coverage",
                ],
                'pcov' => [
                    "@php artisan config:clear --ansi",
                    "@php -d extension=pcov.so -d pcov.enabled=1 artisan test --coverage",
                ],
                'cs' => ['phpcs'],
                'cs-fix' => ['phpcbf'],
                'qa' => ['phpmd src text ./phpmd.xml'],
                'tests' => ['@cs', '@qa', '@test'],
            ],
            'config' => [
                'platform' => [
                    'php' => $php,
                ],
            ],
        ];

        return array_merge_recursive($definition, $newDefinition);
    }
}
