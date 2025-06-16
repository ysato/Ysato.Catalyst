<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Ysato\Catalyst\Scaffold\Processors\ComposerManifestProcessor;
use Ysato\Catalyst\Scaffold\Template\CaseFilters;

class ScaffoldEngineFactory
{
    public function __construct(private readonly string $stubPath)
    {
    }

    /**
     * @param PostProcessorInterface[]|null $postProcessors
     */
    public function create(
        Context $context,
        TemporaryDirectory $sandbox,
        ?Environment $twig = null,
        ?Finder $finder = null,
        ?Filesystem $filesystem = null,
        ?array $postProcessors = null
    ): ScaffoldEngine {
        return new ScaffoldEngine(
            $context,
            $sandbox,
            $twig ?? $this->createDefaultTwig(),
            $finder ?? $this->createDefaultFinder(),
            $filesystem ?? new Filesystem(),
            $postProcessors ?? $this->createDefaultPostProcessors()
        );
    }

    private function createDefaultTwig(): Environment
    {
        $loader = new FilesystemLoader($this->stubPath);
        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new CaseFilters());

        return $twig;
    }

    private function createDefaultFinder(): Finder
    {
        return (new Finder())
            ->ignoreVCSIgnored(false)
            ->ignoreDotFiles(false)
            ->in($this->stubPath)
            ->files();
    }

    /**
     * @return PostProcessorInterface[]
     */
    private function createDefaultPostProcessors(): array
    {
        return [new ComposerManifestProcessor()];
    }
}
