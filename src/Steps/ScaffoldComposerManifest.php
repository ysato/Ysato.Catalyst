<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Steps;

use Composer\Factory;
use Composer\Json\JsonFile;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;

class ScaffoldComposerManifest implements StepInterface
{
    public function __construct(private readonly Filesystem $fs, private readonly TemporaryDirectory $sandbox)
    {
    }

    public function execute(): void
    {
        $origin = new JsonFile(Factory::getComposerFile());
        $definition = $origin->read();

        unset(
            $definition['keywords'],
            $definition['homepage'],
            $definition['description'],
        );

        $definition['name'] = '__Kebab_Vendor__/__Kebab_Package__';
        $definition['license'] = 'proprietary';
        $definition['scripts']['test'] = [
            '@php artisan config:clear --ansi',
            '@php artisan test',
        ];
        $definition['scripts']['coverage'] = [
            '@php artisan config:clear --ansi',
            '@php -d zend_extension=xdebug.so -d xdebug.mode=coverage artisan test --coverage',
        ];
        $definition['scripts']['pcov'] = [
            '@php artisan config:clear --ansi',
            '@php -d extension=pcov.so -d pcov.enabled=1 artisan test --coverage',
        ];
        $definition['scripts']['cs'] = 'phpcs';
        $definition['scripts']['cs-fix'] = 'phpcbf';
        $definition['scripts']['qa'] = ['phpmd src text ./phpmd.xml'];
        $definition['scripts']['tests'] = ['@cs', '@qa', '@test'];
        $definition['config']['platform']['php'] = '__Php__';

        $json = new JsonFile($this->sandbox->path('composer.json'));
        $json->write($definition);
    }
}
