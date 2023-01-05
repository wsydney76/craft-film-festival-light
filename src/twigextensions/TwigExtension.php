<?php

namespace wsydney76\ff\twigextensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
          new TwigFunction('ff', [$this, 'ffFunction'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('ff', [$this, 'ffFunction'])
        ];
    }

    public function ffFunction(string $template) {
        return [
            $template,
            "_ff/$template",
            "@ff/$template"
        ];
    }
}