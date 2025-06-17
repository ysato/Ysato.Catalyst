<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold\Processors;

use Composer\Factory;
use Composer\Json\JsonFile;
use Exception;
use Seld\JsonLint\ParsingException;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use UnexpectedValueException;
use Ysato\Catalyst\Scaffold\PostProcessorInterface;

use function is_array;

class ComposerManifestProcessor implements PostProcessorInterface
{
    /**
     * @throws ParsingException
     */
    public function process(TemporaryDirectory $sandbox): void
    {
        $composerJsonPath = $sandbox->path('composer.json');

        $this->mergeWithOriginal($composerJsonPath);
    }

    /**
     * @throws ParsingException
     * @throws UnexpectedValueException
     * @throws Exception
     */
    private function mergeWithOriginal(string $composerJsonPath): void
    {
        $original = $this->loadOriginalComposerJson();
        $base = $this->removeUnwantedFields($original);

        $composerJson = new JsonFile($composerJsonPath);

        $merged = $this->mergeComposerManifests($base, $composerJson->read());

        $composerJson->write($merged);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ParsingException
     */
    private function loadOriginalComposerJson(): array
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
