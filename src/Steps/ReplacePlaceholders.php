<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Steps;

use Illuminate\Support\Str;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Ysato\Catalyst\Input;

class ReplacePlaceholders implements StepInterface
{
    public function __construct(
        private readonly Filesystem $fs,
        private readonly TemporaryDirectory $sandbox,
        private readonly Finder $finder,
        private readonly Input $input,
    ) {
    }

    public function execute(): void
    {
        $search = [
            '__Vendor__',
            '__Package__',
            '__Php__',
            '__Snake_Vendor__',
            '__Snake_Package__',
            '__Kebab_Vendor__',
            '__Kebab_Package__',
        ];

        $replace = [
            $this->input->vendor,
            $this->input->package,
            $this->input->php,
            Str::snake($this->input->vendor),
            Str::snake($this->input->package),
            Str::kebab($this->input->vendor),
            Str::kebab($this->input->package),
        ];

        $files = $this->finder
            ->ignoreDotFiles(false)
            ->files()
            ->in($this->sandbox->path());

        foreach ($files as $file) {
            $filePath = (string) $file;
            $contents = $this->fs->readFile($filePath);

            $replacedFilePath = str_replace($search, $replace, $filePath);
            $replacedContent = str_replace($search, $replace, $contents);

            $this->fs->dumpFile($replacedFilePath, $replacedContent);
            if ($filePath !== $replacedFilePath) {
                $this->fs->remove($filePath);
            }
        }
    }
}
