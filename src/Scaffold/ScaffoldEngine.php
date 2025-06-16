<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Ysato\Catalyst\Scaffold\Template\EnhancedRenderer;

class ScaffoldEngine
{
    public function __construct(
        private readonly Context $context,
        private readonly TemporaryDirectory $sandbox,
        private readonly Environment $twig,
        private readonly Finder $finder,
        private readonly Filesystem $filesystem,
        /** @var PostProcessorInterface[] */
        private readonly array $postProcessors
    ) {
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function execute(): void
    {
        $renderer = new EnhancedRenderer(
            $this->twig,
            $this->sandbox,
            $this->filesystem,
            $this->finder
        );

        foreach ($this->postProcessors as $processor) {
            $renderer->addProcessor($processor);
        }

        $renderer->renderProject($this->context);
    }
}
