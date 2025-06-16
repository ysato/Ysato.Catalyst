<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold\Processors;

use Composer\Factory;
use Composer\Json\JsonFile;
use Seld\JsonLint\ParsingException;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Ysato\Catalyst\Scaffold\PostProcessorInterface;

use function file_put_contents;
use function is_array;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class ComposerManifestProcessor implements PostProcessorInterface
{
    /**
     * @throws ParsingException
     */
    public function process(TemporaryDirectory $sandbox): void
    {
        $overridesPath = $sandbox->path('composer.json');

        $this->mergeWithOriginal($overridesPath);
    }

    /**
     * @throws ParsingException
     */
    private function mergeWithOriginal(string $composerPath): void
    {
        $original = $this->loadOriginalComposer();
        $base = $this->removeUnwantedFields($original);
        $overrides = (new JsonFile($composerPath))->read();

        $merged = $this->mergeComposerManifests($base, $overrides);

        file_put_contents($composerPath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ParsingException
     */
    private function loadOriginalComposer(): array
    {
        return (new JsonFile(Factory::getComposerFile()))->read();
    }

    /**
     * @param array<string, mixed> $composer
     *
     * @return array<string, mixed>
     */
    private function removeUnwantedFields(array $composer): array
    {
        unset(
            $composer['keywords'],
            $composer['homepage'],
            $composer['description'],
        );

        return $composer;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    private function mergeComposerManifests(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeComposerManifests($base[$key], $value);

                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }
}
