<?php

declare(strict_types=1);

namespace Tests\Scaffold;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Ysato\Catalyst\Scaffold\Context;
use Ysato\Catalyst\Scaffold\Processors\ComposerManifestProcessor;
use Ysato\Catalyst\Scaffold\ScaffoldEngineFactory;
use Ysato\Catalyst\Scaffold\Template\CaseFilters;
use Ysato\Catalyst\Testing\MatchesSnapshots;

use function dirname;
use function file_get_contents;
use function putenv;

class ScaffoldEngineTest extends TestCase
{
    use MatchesSnapshots;

    private readonly TemporaryDirectory $sandbox;

    private readonly string $templatePath;

    protected function setUp(): void
    {
        $this->sandbox = (new TemporaryDirectory())
//            ->location(__DIR__ . '/__sandbox__') // デバッグ時有効化
            ->deleteWhenDestroyed() // デバッグ時無効化
            ->create();

        $this->templatePath = dirname(__DIR__) . '/../stubs';

        $composerPath = dirname(__DIR__) . '/project/composer.json';
        putenv("COMPOSER={$composerPath}");
    }

    protected function tearDown(): void
    {
        unset($_ENV['COMPOSER']);

        $this->sandbox->delete();
    }

    /**
     * @dataProvider contextProvider
     */
    public function testExecute(
        string $vendor,
        string $package,
        string $php,
        ?string $caFilePath,
        string $originalGitignore
    ): void {
        $context = new Context($vendor, $package, $php, $caFilePath, $originalGitignore);

        $loader = new FilesystemLoader(dirname(__DIR__) . '/../stubs');
        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new CaseFilters());

        $finder = (new Finder())
            ->ignoreVCSIgnored(false)
            ->ignoreDotFiles(false)
            ->in($this->templatePath)
            ->files();

        $fs = new Filesystem();

        $processors = [new ComposerManifestProcessor()];

        $factory = new ScaffoldEngineFactory($this->templatePath);
        $engine = $factory->create($context, $this->sandbox, $twig, $finder, $fs, $processors);

        $engine->execute();

        $this->assertMatchesSnapshot($this->sandbox->path());
    }

    /**
     * @return array<string, array<string|null>>
     */
    public static function contextProvider(): array
    {
        $gitignorePath = dirname(__DIR__) . '/project/.gitignore';
        $originalGitignore = file_get_contents($gitignorePath);

        return [
            'basic' => ['vendor', 'package', '8.2', null, $originalGitignore],
            'with_ca' => ['vendor', 'package', '8.2', './certs/cert.pem', $originalGitignore],
            'php83' => ['vendor', 'package', '8.3', null, $originalGitignore],
            'different_vendor' => ['acme', 'tool', '8.2', null, $originalGitignore],
        ];
    }
}
