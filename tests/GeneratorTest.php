<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use PHPUnit\Framework\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Exception\IOException;
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
            __DIR__ . '/Fake/stubs/ide',
            $this->temporaryDirectory->path()
        );

        $SUT->generate($this->laravelDir->path());

        $this->assertFileEquals(
            __DIR__ . '/Fake/expected/.editorconfig',
            $this->laravelDir->path('.editorconfig')
        );
    }

    public function test_generate_after_generate()
    {
        $SUT = new Generator(
            $this->filesystem,
            $this->finder,
            $this->temporaryDirectory,
            __DIR__ . '/Fake/stubs/ide',
            $this->temporaryDirectory->path()
        );

        $newLaravelDir = (new TemporaryDirectory())
            ->deleteWhenDestroyed()
            ->create();

        $SUT->generate($this->laravelDir->path());

        $this->assertFileEquals(__DIR__ . '/Fake/expected/.editorconfig', $this->laravelDir->path('.editorconfig'));

        $this->expectException(IOException::class);
        $SUT->generate($newLaravelDir->path());
    }

    public function test_dumpFile()
    {
        $SUT = new Generator(
            $this->filesystem,
            $this->finder,
            $this->temporaryDirectory,
            __DIR__ . '/Fake/stubs',
            $this->temporaryDirectory->path()
        );

        $contents = <<<EOT
/idea/*
!/.idea/inspectionProfiles

EOT;

        $SUT->dumpFile('.gitignore', $contents);

        $this->assertStringEqualsFile($this->temporaryDirectory->path('.gitignore'), $contents);
    }

    public function test_appendFile()
    {
        $SUT = new Generator(
            $this->filesystem,
            $this->finder,
            $this->temporaryDirectory,
            __DIR__ . '/Fake/stubs',
            $this->temporaryDirectory->path()
        );

        $contents = <<<EOT
/idea/*
!/.idea/inspectionProfiles

EOT;

        $newContents = <<<EOT
.phpcs_cache.php

EOT;

        $SUT->dumpFile('.gitignore', $contents);
        $SUT->appendToFile('.gitignore', $newContents);

        $this->assertFileEquals(__DIR__ . '/Fake/expected/.gitignore', $this->temporaryDirectory->path('.gitignore'));
    }

    public function test_replacePlaceHolder()
    {
        $SUT = new Generator(
            $this->filesystem,
            $this->finder,
            $this->temporaryDirectory,
            __DIR__ . '/Fake/stubs/architecture-src',
            $this->temporaryDirectory->path()
        );

        $SUT->replacePlaceHolder('Ysato', 'Catalyst');

        $this->assertFileEquals(
            __DIR__ . '/Fake/expected/src/Catalyst.php',
            $this->temporaryDirectory->path('src/Catalyst.php')
        );
    }

    /**
     * @test
     * @link https://github.com/ysato/Ysato.Catalyst/commit/4e5c1538e946c074725ebe80ab13da777dd7c8b4
     */
    public function ドットファイルのプレースホルダーも置き換えられるか()
    {
        $SUT = new Generator(
            $this->filesystem,
            $this->finder,
            $this->temporaryDirectory,
            __DIR__ . '/Fake/stubs/.github',
            $this->temporaryDirectory->path()
        );

        $SUT->replacePlaceHolder('Ysato', 'Catalyst');

        $this->assertFileEquals(
            __DIR__ . '/Fake/expected/.github/.gitkeep',
            $this->temporaryDirectory->path('.github/.gitkeep')
        );
    }
}
