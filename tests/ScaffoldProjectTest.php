<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Ysato\Catalyst\Input;
use Ysato\Catalyst\Steps\GenerateGitignore;
use Ysato\Catalyst\Steps\PrepareSandboxFromStubs;
use Ysato\Catalyst\Steps\ReplacePlaceholders;
use Ysato\Catalyst\Steps\ScaffoldComposerManifest;
use Ysato\Catalyst\Steps\ScaffoldDocker;

class ScaffoldProjectTest extends TestCase
{
    private Filesystem $fs;

    private Finder $finder;

    private TemporaryDirectory $sandbox;

    private Application&MockObject $app;

    public function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->finder = new Finder();
        $this->sandbox = (new TemporaryDirectory())
            ->deleteWhenDestroyed()
            ->create();
        $this->app = $this->createMock(Application::class);

        putenv('COMPOSER=' . __DIR__ . '/project/composer.json');
    }

    public function tearDown(): void
    {
        $this->sandbox->delete();

        unset($_ENV['COMPOSER']);
    }

    public function testExecuteWithCa()
    {
        $this->app
            ->expects($this->once())
            ->method('basePath')
            ->with('.gitignore')
            ->willReturn(__DIR__ . '/project/.gitignore');

        $input = new Input('Acme', 'Blog', '8.2', './certs/certificate.pem');

        $steps = [
            new PrepareSandboxFromStubs($this->fs, $this->sandbox, __DIR__ . '/stubs'),
            new GenerateGitignore($this->fs, $this->sandbox, $this->app),
            new ScaffoldComposerManifest($this->fs, $this->sandbox),
            new ScaffoldDocker($this->fs, $this->sandbox, $input),
            new ReplacePlaceholders($this->fs, $this->sandbox, $this->finder, $input),
        ];

        (new Collection($steps))
            ->each(fn($step) => $step->execute());

        $expected = $this->finder
            ->ignoreDotFiles(false)
            ->in(__DIR__ . '/expected')
            ->files();

        foreach ($expected as $file) {
            $this->assertFileEquals((string) $file, $this->sandbox->path($file->getRelativePathname()));
        }
    }
}
