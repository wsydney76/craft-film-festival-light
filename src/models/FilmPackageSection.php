<?php

namespace wsydney76\ff\models;

use Illuminate\Support\Collection;
use wsydney76\package\models\PackageSection;

/**
 * Experimental: Use film as package
 * https://github.com/wsydney76/craft-package#use-existing-sections-as-a-package
 */
class FilmPackageSection extends PackageSection
{
    public function getFormTemplates(): Collection
    {
        return parent::getFormTemplates()
            ->filter(fn($template) => $template !== '/package/forms/attach-new.twig')
            ->push('ff-light/_package/screenings-form.twig');
    }
}