<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Scaffold\Template;

use Illuminate\Support\Str;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CaseFilters extends AbstractExtension
{
    /** @return TwigFilter[] */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('pascal', [$this, 'toPascal']),
            new TwigFilter('kebab', [$this, 'toKebab']),
            new TwigFilter('snake', [$this, 'toSnake']),
        ];
    }

    public function toPascal(string $value): string
    {
        return Str::studly($value);
    }

    public function toKebab(string $value): string
    {
        return Str::kebab($value);
    }

    public function toSnake(string $value): string
    {
        return Str::snake($value);
    }
}
