<?php

namespace wsydney76\ff\models;

use craft\base\Model;

/**
 * Film Festival settings
 */
class Settings extends Model
{

    public string $templateRoot = '';

    public bool $enableSponsors = true;
    public bool $enableTopics = true;
    public bool $enableAwards = true;

    public bool $enableSections = true;
    public bool $enableScript = true;
    public bool $enableCamera = true;
    public bool $enableCreditedWith = true;

    public bool $enableFilmPoster = true;
    public bool $enableGenres = true;

    public bool $enableCountries = true;
    public bool $enableLanguages = true;
    public bool $enableSubtitleLanguages = true;

    public bool $enableDiaries = true;

    public int $programPageLimit = 20;

    public function rules(): array
    {
        return [
            ['programPageLimit', 'integer', 'min' => 2, 'max' => 50]
        ];
    }
}
