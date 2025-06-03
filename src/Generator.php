<?php

declare(strict_types=1);

namespace Ysato\Catalyst;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Generator
{
    public function __construct(
        private readonly FileSystem $fs,
        private readonly Finder $finder,
        private TemporaryDirectory $temporaryDirectory,
        private readonly string $stubsPath,
        private readonly string $tempPath,
        private bool $isMirrored = false,
    ) {
    }

    public function replacePlaceHolder(string $vendor, string $package): self
    {
        $this->mirrorToTemp();

        $files = $this->finder->files()->in($this->tempPath);

        foreach ($files as $file) {
            $search = ['__Vendor__', '__Package__'];
            $replace = [$vendor, $package];
            $filePath = (string) $file;
            $contents = $this->fs->readFile($filePath);

            $newFilePath = str_replace($search, $replace, $filePath, $count);
            $newContents = str_replace($search, $replace, $contents);

            $this->fs->dumpFile($newFilePath, $newContents);
            if ($count !== null) {
                $this->fs->remove($filePath);
            }
        }

        return $this;
    }

    public function appendToFile(string $filename, string $contents, bool $lock = false): self
    {
        $this->mirrorToTemp();

        $this->fs->appendToFile($this->temporaryDirectory->path($filename) . $filename, $contents, $lock);

        return $this;
    }

    public function generate(string $path): void
    {
        $this->mirrorToTemp();

        $this->fs->mirror($this->tempPath, $path, options: ['override' => true]);

        $this->temporaryDirectory->delete();
    }

    public function getFilesystem(): FileSystem
    {
        return $this->fs;
    }

    private function mirrorToTemp()
    {
        if ($this->isMirrored) {
            return;
        }

        $this->fs->mirror($this->stubsPath, $this->tempPath, options: ['override' => true]);
        $this->isMirrored = true;
    }
}