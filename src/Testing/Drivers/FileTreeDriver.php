<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Testing\Drivers;

use Illuminate\Support\Collection;
use Override;
use PHPUnit\Framework\Assert;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Ysato\Catalyst\Testing\DriverInterface;

use function assert;
use function file_get_contents;
use function implode;
use function is_string;
use function str_repeat;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

class FileTreeDriver implements DriverInterface
{
    public function __construct(private readonly Differ $differ = new Differ(new UnifiedDiffOutputBuilder()))
    {
    }

    #[Override]
    public function match(string $expected, string $actual): void
    {
        $differences = $this->discoverDifferences($expected, $actual);

        $message = 'File tree snapshot matches snapshot';
        if ($differences->isNotEmpty()) {
            $message = $this->formatDifferences($differences->all(), $expected, $actual);
        }

        Assert::assertCount(0, $differences->all(), $message);
    }

    /**
     * @return Collection<int, string>
     * @psalm-return Collection<int, non-empty-string>
     * @phpstan-return Collection<int, string>
     */
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

    /** @return Collection<string, SplFileInfo> */
    private function discoverFiles(string $path): Collection
    {
        $files = (new Finder())
            ->ignoreVCSIgnored(false)
            ->ignoreDotFiles(false)
            ->in($path)
            ->files();

        return new Collection($files);
    }

    /**
     * @param Collection<string, SplFileInfo> $expectedFiles
     * @param Collection<string, SplFileInfo> $actualFiles
     *
     * @return Collection<int, string>
     * @psalm-return Collection<int, non-empty-string>&static
     */
    private function discoverExtraFiles(Collection $expectedFiles, Collection $actualFiles): Collection
    {
        return $actualFiles
            ->diffUsing($expectedFiles, static function (SplFileInfo $a, SplFileInfo $b) {
                return $a->getRelativePathname() <=> $b->getRelativePathname();
            })
            ->values()
            ->map(static fn(SplFileInfo $file) => "Extra file: {$file->getRelativePathname()}");
    }

    /**
     * @param Collection<string, SplFileInfo> $expectedFiles
     * @param Collection<string, SplFileInfo> $actualFiles
     *
     * @return Collection<int, string>
     * @psalm-return Collection<int, non-empty-string>&static
     */
    private function discoverMissingFiles(Collection $expectedFiles, Collection $actualFiles): Collection
    {
        return $expectedFiles
            ->diffUsing($actualFiles, static function (SplFileInfo $a, SplFileInfo $b) {
                return $a->getRelativePathname() <=> $b->getRelativePathname();
            })
            ->values()
            ->map(static fn(SplFileInfo $file) => "Missing file: {$file->getRelativePathname()}");
    }

    /**
     * @param Collection<string, SplFileInfo> $expectedFiles
     * @param Collection<string, SplFileInfo> $actualFiles
     *
     * @return Collection<int, string>
     * @psalm-return Collection<int<0, max>, non-empty-string>
     * @phpstan-return Collection<int, non-falsy-string>
     */
    private function discoverContentDiffs(
        Collection $expectedFiles,
        Collection $actualFiles,
        string $actual,
    ): Collection {
        $commonFiles = $expectedFiles->intersectUsing($actualFiles, static function (SplFileInfo $a, SplFileInfo $b) {
            return $a->getRelativePathname() <=> $b->getRelativePathname();
        });

        $diffs = [];
        foreach ($commonFiles as $file) {
            $actualContent = file_get_contents($actual . DIRECTORY_SEPARATOR . $file->getRelativePathname());
            assert(is_string($actualContent), 'Failed to read file content');

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

    /** @param string[] $differences */
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
