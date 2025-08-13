<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use RuntimeException;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Ysato\Catalyst\Scaffold\Template\CaseFilters;
use Ysato\Catalyst\Scaffold\Template\Renderer;

use function array_key_exists;
use function array_merge;
use function array_unshift;
use function assert;
use function is_array;
use function json_decode;
use function json_encode;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;

class Scaffolder
{
    public function __construct(
        private readonly SandboxInterface $sandbox,
        private readonly Renderer $renderer,
        private readonly string $stubPath,
        private readonly string $basePath,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public static function create(string $basePath, string $stubPath): Scaffolder
    {
        $temporaryDirectory = new TemporaryDirectory();
        $sandbox = new Sandbox($temporaryDirectory, $basePath);

        $loader = new FilesystemLoader($stubPath);
        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new CaseFilters());

        $renderer = new Renderer($twig);

        return new Scaffolder($sandbox, $renderer, $stubPath, $basePath);
    }

    public function scaffold(Input $input): void
    {
        $context = Context::fromInputAndGitignorePath($input, $this->basePath . DIRECTORY_SEPARATOR . '.gitignore');

        $this->sandbox->create();

        try {
            $this->sandbox->execute($this->copyStubsWithRenderedNames($context));

            $this->sandbox->execute($this->generateComposerJson($context->php));

            $this->sandbox->execute($this->renderVariablesInSandboxFiles($context));

            $this->sandbox->commit();
        } finally {
            $this->sandbox->delete();
        }
    }

    private function copyStubsWithRenderedNames(Context $context): callable
    {
        return function (string $path) use ($context) {
            $files = (new Finder())
                ->ignoreVCSIgnored(false)
                ->ignoreDotFiles(false)
                ->in($this->stubPath)
                ->files();

            foreach ($files as $file) {
                $content = $this->filesystem->readFile($file->getRealPath());

                $rendered = $this->renderer->render($file->getRelativePathname(), $context);

                $this->filesystem->dumpFile($path . DIRECTORY_SEPARATOR . $rendered, $content);
            }
        };
    }

    private function generateComposerJson(string $php): callable
    {
        return function (string $path) use ($php) {
            if (! $this->filesystem->exists($this->basePath . DIRECTORY_SEPARATOR . 'composer.json')) {
                throw new RuntimeException('composer.json file not found in the project root');
            }

            /**
             * @phpstan-var  array{
             *     keywords?: string[],
             *     homepage?: string,
             *     description?: string,
             *     name?: string,
             *     license?: string,
             *     require?: array<string, string>,
             *     autoload?: array{"psr-4?": array<string, string>},
             *     scripts?: array<string, string|string[]>,
             *     config?: array{platform?: array{php?: string}}
             * }
             */
            $content = json_decode(
                $this->filesystem->readFile($this->basePath . DIRECTORY_SEPARATOR . 'composer.json'),
                true,
                flags: JSON_THROW_ON_ERROR,
            );

            unset($content['keywords'], $content['homepage'], $content['description']);

            $content['name'] = '{{ vendor|kebab }}/{{ package|kebab }}';
            $content['license'] = 'proprietary';

            $require = array_key_exists('require', $content) ? $content['require'] : [];
            $content['require'] = array_merge($require, ['php' => '^{{ php }}']);

            assert(array_key_exists('autoload', $content), 'autoload section must exist in composer.json');
            $content['autoload'] = $this->buildAutoloadSection($content['autoload']);

            assert(array_key_exists('scripts', $content), 'scripts section must exist in composer.json');
            $content['scripts'] = $this->buildScriptsSection($content['scripts'], $php);

            assert(array_key_exists('config', $content), 'config section must exist in composer.json');
            $content['config'] = $this->buildConfigSection($content['config']);

            $this->filesystem->dumpFile(
                $path . DIRECTORY_SEPARATOR . 'composer.json',
                json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL,
            );
        };
    }

    /**
     * @param array<string, string|string[]> $scripts
     *
     * @return non-empty-array<string, string|string[]>
     */
    private function buildScriptsSection(array $scripts, string $php): array
    {
        $newScripts = [
            'test' => [
                '@php artisan config:clear --ansi',
                '@php artisan test',
            ],
            'coverage' => [
                '@php artisan config:clear --ansi',
                '@php -d zend_extension=xdebug.so -d xdebug.mode=coverage ./vendor/bin/phpunit --coverage-text --coverage-html=build/coverage',
            ],
            'pcov' => [
                '@php artisan config:clear --ansi',
                '@php -d extension=pcov.so -d pcov.enabled=1 ./vendor/bin/phpunit --coverage-text --coverage-html=build/coverage --coverage-clover=build/coverage.xml',
            ],
            'cs' => 'phpcs',
            'cs-fix' => 'phpcbf',
            'phpmd' => 'phpmd app,src text ./phpmd.xml',
            'qa' => ['phpstan --memory-limit=-1', 'psalm --no-cache'],
            'spectate' => 'ENABLE_SPECTATION_REPORT=true ./vendor/bin/phpunit --no-progress --no-results',
            'lints' => ['@cs', '@qa'],
            'tests' => ['@lints', '@test'],
        ];

        if ($php !== '8.4') {
            array_unshift($newScripts['qa'], '@phpmd');
        }

        return array_merge($scripts, $newScripts);
    }

    /**
     * @param array<string, mixed> $autoload
     *
     * @return non-empty-array<string, mixed>
     */
    private function buildAutoloadSection(array $autoload): array
    {
        $psr4 = [];
        if (array_key_exists('psr-4', $autoload) && is_array($autoload['psr-4'])) {
            $psr4 = $autoload['psr-4'];
        }

        $autoload['psr-4'] = array_merge(
            $psr4,
            ['{{ vendor|pascal }}\\{{ package|pascal }}\\' => 'src/'],
        );

        return $autoload;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return non-empty-array<string, mixed>
     */
    private function buildConfigSection(array $config): array
    {
        $platform = [];
        if (array_key_exists('platform', $config) && is_array($config['platform'])) {
            $platform = $config['platform'];
        }

        $config['platform'] = array_merge($platform, ['php' => '{{ php }}']);

        return $config;
    }

    private function renderVariablesInSandboxFiles(Context $context): callable
    {
        return function (string $path) use ($context) {
            $files = (new Finder())
                ->ignoreVCSIgnored(false)
                ->ignoreDotFiles(false)
                ->in($path)
                ->files();

            foreach ($files as $file) {
                $content = $this->filesystem->readFile($file->getRealPath());
                $this->filesystem->dumpFile($file->getRealPath(), $this->renderer->render($content, $context));
            }
        };
    }
}
