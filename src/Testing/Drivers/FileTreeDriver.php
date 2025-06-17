<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Testing\Drivers;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Ysato\Catalyst\Testing\DriverInterface;

use function file_get_contents;
use function implode;
use function str_repeat;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

class FileTreeDriver implements DriverInterface
{
    public function __construct(private readonly Differ $differ = new Differ(new UnifiedDiffOutputBuilder()))
    {
    }

    public function match(string $expected, string $actual): void
    {
        $differences = $this->discoverDifferences($expected, $actual);

        if ($differences->isNotEmpty()) {
            $message = $this->formatDifferences($differences->all(), $expected, $actual);

            Assert::fail($message);
        }

        Assert::assertTrue(true, 'File tree matches snapshot');
    }

    private function discoverDifferences(string $expected, string $actual): Collection
    {
        $expectedFiles = $this->discoverFiles($expected);
        $actualFiles = $this->discoverFiles($actual);

        $missingFiles = $this->discoverMissingFiles($expectedFiles, $actualFiles);
        $extraFiles = $this->discoverExtraFiles($expectedFiles, $actualFiles);
        $contentDiffs = $this->discoverContentDiffs($expectedFiles, $actualFiles, $actual);

        return $missingFiles
            ->merge($extraFiles)
            ->merge($contentDiffs);
    }

    private function discoverFiles(string $path): Collection
    {
        $files = (new Finder())
            ->ignoreVCSIgnored(false)
            ->ignoreDotFiles(false)
            ->in($path)
            ->files();

        return new Collection($files);
    }

    private function discoverExtraFiles(Collection $expectedFiles, Collection $actualFiles): Collection
    {
        return $actualFiles
            ->diffUsing($expectedFiles, static function (SplFileInfo $a, SplFileInfo $b) {
                return $a->getRelativePathname() <=> $b->getRelativePathname();
            })
            ->map(static fn (SplFileInfo $file) => "Extra file: {$file->getRelativePathname()}");
    }

    private function discoverMissingFiles(Collection $expectedFiles, Collection $actualFiles): Collection
    {
        return $expectedFiles
            ->diffUsing($actualFiles, static function (SplFileInfo $a, SplFileInfo $b) {
                return $a->getRelativePathname() <=> $b->getRelativePathname();
            })
            ->map(static fn (SplFileInfo $file) => "Missing file: {$file->getRelativePathname()}");
    }

    private function discoverContentDiffs(
        Collection $expectedFiles,
        Collection $actualFiles,
        string $actual
    ): Collection {
        $commonFiles = $expectedFiles->intersectUsing($actualFiles, static function (SplFileInfo $a, SplFileInfo $b) {
            return $a->getRelativePathname() <=> $b->getRelativePathname();
        });

        $diffs = [];
        foreach ($commonFiles as $file) {
            $actualContent = file_get_contents($actual . DIRECTORY_SEPARATOR . $file->getRelativePathname());

            if ($file->getContents() === $actualContent) {
                continue;
            }

            $diff = $this->differ->diff($file->getContents(), $actualContent);

            $diffs[] = <<<DIFF
File content differs: {$file->getRelativePathname()}
{$diff}
DIFF;
        }

        return new Collection($diffs);
    }

    /**
     * @param string[] $differences
     */
    private function formatDifferences(array $differences, string $expected, string $actual): string
    {
        $line = str_repeat('=', 50);
        $diffs = implode(PHP_EOL, $differences);

        return <<<HEADER
File tree snapshot does not match:
Expected: $expected
Actual: $actual
$line
$diffs
HEADER;
    }
}
