<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold\Template;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Ysato\Catalyst\Scaffold\Context;
use Ysato\Catalyst\Scaffold\PostProcessorInterface;

use function array_keys;
use function array_values;
use function is_dir;
use function str_replace;

class EnhancedRenderer
{
    private const PLACEHOLDER_MAP = [
        '__Package__' => '{{ package|pascal }}',
        '__Vendor__' => '{{ vendor|pascal }}',
    ];

    /** @var PostProcessorInterface[] */
    private array $processors = [];

    public function __construct(
        private readonly Environment $twig,
        private readonly TemporaryDirectory $sandbox,
        private readonly Filesystem $fs,
        private readonly Finder $finder
    ) {
    }

    public function addProcessor(PostProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderProject(Context $context): void
    {
        foreach ($this->finder as $template) {
            $this->renderTemplate($template, $context);
        }

        $this->runPostProcessors();
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function renderTemplate(SplFileInfo $template, Context $context): void
    {
        $relativePath = $template->getRelativePathname();

        // テンプレートをレンダリング
        $content = $this->twig->render($relativePath, $context->toArray());

        // 出力パスを決定（ファイル名のプレースホルダー変換）
        $outputPath = $this->renderOutputPath($relativePath, $context);
        $fullPath = $this->sandbox->path($outputPath);

        // ディレクトリが既に存在する場合は削除
        if (is_dir($fullPath)) {
            $this->fs->remove($fullPath);
        }

        $this->fs->dumpFile($fullPath, $content);
    }

    /**
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function renderOutputPath(string $relativePath, Context $context): string
    {
        $search = array_keys(self::PLACEHOLDER_MAP);
        $replace = array_values(self::PLACEHOLDER_MAP);

        $template = str_replace($search, $replace, $relativePath);

        return $this->twig
            ->createTemplate($template)
            ->render($context->toArray());
    }

    private function runPostProcessors(): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($this->sandbox);
        }
    }
}
