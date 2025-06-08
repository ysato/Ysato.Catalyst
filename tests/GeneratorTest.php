<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use PHPUnit\Framework\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GeneratorTest extends TestCase
{
    private Generator $SUT;

    private Filesystem $filesystem;

    private Finder $finder;

    private TemporaryDirectory $temporaryDirectory;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->finder = $this->createMock(Finder::class);
        $this->temporaryDirectory = $this->createMock(TemporaryDirectory::class);

        $this->SUT = new Generator(
            $this->filesystem,
            $this->finder,
            $this->temporaryDirectory,
            '',
            ''
        );
    }

    public function testIsInstanceOfGenerator(): void
    {
        $actual = $this->SUT;
        $this->assertInstanceOf(Generator::class, $actual);
    }
}
