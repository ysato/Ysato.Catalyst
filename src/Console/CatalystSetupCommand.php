<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Closure;
use Composer\Factory;
use Composer\Json\JsonFile;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use function Laravel\Prompts\multiselect;

class CatalystSetupCommand extends Command
{
    private const METADATA = 1;

    private const ARCHITECTURE_SRC = 2;

    private const QA = 3;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalyst:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the base architecture and QA tool configurations for a Laravel project.';

    private string $vendor = '';

    private string $package = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissions = multiselect(
            label: 'What needs to be set up?',
            options: [
                self::METADATA => 'composer.json metadata',
                self::ARCHITECTURE_SRC => 'src architecture',
                self::QA => 'quality assurance tools',
            ],
            hint: 'Press the space key to select.'
        );

        $metaOrSrc = function ($value) {
            return in_array($value, [self::METADATA, self::ARCHITECTURE_SRC], true);
        };

        if ($this->arrayAny($permissions, $metaOrSrc)) {
            $this->vendor = $this->ask('What is the vendor name ?', 'MyVendor');
            $this->package = $this->ask('What is the package name ?', 'MyPackage');
        }

        if (in_array(self::METADATA, $permissions, true)) {
            $this->configureProjectMetadata();
        }

        if (in_array(self::ARCHITECTURE_SRC, $permissions, true)) {
            $this->setupArchitectureSrc();
        }

        if (in_array(self::QA, $permissions, true)) {
            $this->copyQABaselinesAndIDESettings();
        }

        $this->info('Project setup completed successfully.');
    }

    private function configureProjectMetadata(): void
    {
        $json = new JsonFile(Factory::getComposerFile());

        try {
            $definition = $json->read();
        } catch (ParsingException $e) {
            throw new RuntimeException('Error reading composer.json: ' . $e->getMessage());
        }

        unset(
            $definition['keywords'],
            $definition['homepage'],
            $definition['description'],
        );

        $definition['name'] = $this->getPackageName();
        $definition['license'] = 'proprietary';

        try {
            $json->write($definition);
        } catch (Exception $e) {
            throw new RuntimeException('Error parsing composer.json: ' . $e->getMessage());
        }

        $this->info("composer.json for {$definition['name']} is configured.\n");
    }

    private function setupArchitectureSrc(): void
    {
        $json = new JsonFile(Factory::getComposerFile());

        try {
            $definition = $json->read();
        } catch (ParsingException $e) {
            throw new RuntimeException('Error reading composer.json: ' . $e->getMessage());
        }

        $namespace = "$this->vendor\\$this->package\\";
        if (! $this->isAlreadyDefined($namespace, $definition['autoload']['psr-4'] ?? null)) {
            $definition['autoload']['psr-4'][$namespace] = 'src/';
        }

        try {
            $json->write($definition);
        } catch (Exception $e) {
            throw new RuntimeException('Error parsing composer.json: ' . $e->getMessage(), $e->getCode(), $e);
        }

        $from = dirname(__DIR__) . '/stubs';
        $to = dirname(__DIR__) . '/tmp';

        $fs = new Filesystem();

        try {
            $fs->mirror($from, $to, options: ['override' => true]);
            $fs->rename("$to/src/Skeleton.php", "$to/src/{$this->package}.php", true);
        } catch (IOException $e) {
            throw new InvalidArgumentException("Could not copy $from", $e->getCode(), $e);
        }

        try {
            $this->rename($to, $this->vendor, $this->package);
        } catch (IOException $e) {
            throw new InvalidArgumentException("Could not rename $to", $e->getCode(), $e);
        }

        try {
            $fs->mirror($to, $this->laravel->basePath(), options: ['override' => true]);
        } catch (IOException $e) {
            throw new InvalidArgumentException("Could not copy $to", $e->getCode(), $e);
        }

        try {
            $fs->remove($to);
        } catch (IOException $e) {
            throw new InvalidArgumentException("Could not remove $to", $e->getCode(), $e);
        }

        $this->info('src architecture is set up.');
    }

    private function copyQABaselinesAndIDESettings(): void
    {
        $fs = new Filesystem();

        try {
            $fs->mirror(dirname(__DIR__) . '/baselines', $this->laravel->basePath(), options: ['override' => true]);
        } catch (IOException $e) {
            throw new InvalidArgumentException("Could not copy QA baselines", $e->getCode(), $e);
        }

        try {
            $fs->mirror(dirname(__DIR__) . '/.idea', $this->laravel->basePath('/.idea'), options: ['override' => true]);
        } catch (IOException $e) {
            throw new InvalidArgumentException("Could not copy QA baselines", $e->getCode(), $e);
        }

        $this->addQAScripts();
        $this->addSomeGitIgnoreLines();
    }

    private function addQAScripts(): void
    {
        $json = new JsonFile(Factory::getComposerFile());

        try {
            $definition = $json->read();
        } catch (ParsingException $e) {
            throw new RuntimeException('Error reading composer.json: ' . $e->getMessage());
        }

        $definition['scripts']['cs'] = 'phpcs';
        $definition['scripts']['cs-fix'] = 'phpcbf';
        $definition['scripts']['qa'] = 'phpmd src text ./phpmd.xml';
        $definition['scripts']['tests'] = ['@cs', '@qa', '@test'];

        try {
            $json->write($definition);
        } catch (Exception $e) {
            throw new RuntimeException('Error parsing composer.json: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function isAlreadyDefined(string $namespace, mixed $definition): bool
    {
        return isset($definition) && array_key_exists($namespace, $definition);
    }

    private function rename(string $path, string $vendor, string $package): void
    {
        $finder = new Finder();

        $files = $finder->files()->in($path);

        foreach ($files as $file) {
            $filePath = (string) $file;

            $search = ['__Vendor__', '__Package__'];
            $replace = [$vendor, $package];
            $contents = (string) file_get_contents($filePath);

            $newContents = str_replace($search, $replace, $contents);

            file_put_contents($filePath, $newContents);
        }
    }

    private function addSomeGitIgnoreLines(): void
    {
        $contents = (string) file_get_contents($this->laravel->basePath('/.gitignore'));
        $linesToPrepend = (string) file_get_contents(dirname(__DIR__) . '/gitignore');

        file_put_contents($this->laravel->basePath('/.gitignore'), $linesToPrepend . $contents);
    }

    private function getPackageName()
    {
        return sprintf('%s/%s', Str::kebab($this->vendor), Str::kebab($this->package));
    }

    private function arrayAny(array $values, Closure $closure): bool
    {
        foreach ($values as $value) {
            if ($closure($value) === true) {
                return true;
            }
        }

        return false;
    }
}