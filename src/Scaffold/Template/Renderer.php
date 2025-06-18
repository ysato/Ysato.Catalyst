<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold\Template;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Ysato\Catalyst\Scaffold\Context;

use function array_keys;
use function array_values;
use function str_replace;

class Renderer
{
    private const PLACEHOLDER_MAP = ['__Package__' => '{{ package|pascal }}'];

    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function render(string $content, Context $context): string
    {
        $search = array_keys(self::PLACEHOLDER_MAP);
        $replace = array_values(self::PLACEHOLDER_MAP);

        $template = str_replace($search, $replace, $content);

        return $this->twig->createTemplate($template)->render($context->toArray());
    }
}
