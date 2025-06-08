<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use PHPUnit\Framework\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GeneratorTest extends TestCase
{
    private Filesystem $filesystem;

    private Finder $finder;

    private TemporaryDirectory $temporaryDirectory;

    private TemporaryDirectory $laravelDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
        $this->temporaryDirectory = (new TemporaryDirectory())
            ->deleteWhenDestroyed()
            ->create();
        $this->laravelDir = (new TemporaryDirectory())
            ->deleteWhenDestroyed()
            ->create();
    }

    public function test_generate()
    {
        $SUT = new Generator(
            $this->filesystem,
            $this->finder,
            $this->temporaryDirectory,
            __DIR__ . '/Fake/stubs/phpmd',
            $this->temporaryDirectory->path()
        );

        $SUT->generate($this->laravelDir->path());

        $this->assertFileEquals(
            __DIR__ . '/Fake/expected/phpmd.xml',
            $this->laravelDir->path('phpmd.xml')
        );
    }
}
