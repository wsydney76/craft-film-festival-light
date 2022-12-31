<?php

namespace wsydney76\ff\console\controllers;

use Craft;
use craft\console\Controller;
use DateTime;
use Faker\Factory;
use modules\main\console\controllers\SeedController;
use wsydney76\ff\Plugin;
use yii\console\ExitCode;

/**
 * Ff Install controller
 */
class InstallController extends SeedController
{
    public $defaultAction = 'index';

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        switch ($actionID) {
            case 'index':
                // $options[] = '...';
                break;
        }
        return $options;
    }

    /**
     * ff/ff-install command
     */
    public function actionIndex(): int
    {
        Plugin::getInstance()->migrationService->install();

        return ExitCode::OK;
    }

    public function actionSeed(): int
    {

        if (!$this->confirm("Create starter content?")) {
            return ExitCode::OK;
        }

        $faker = Factory::create();

        $person1 = $this->createEntry([
            'section' => 'person',
            'type' => 'default',
            'title' => 'Erna Klawuppke',
            'slug' => 'erna-klawuppke',
            'fields' => [
                'firstName' => 'Erna',
                'lastName' => 'Klawuppke',
                'tagline' => 'Shooting Star',
                'birthday' => new DateTime('1986-04-09'),
                'shortBio' => 'Erna studied in Kleinfinstenich and New York',
                'filmography' => 'Tbd.',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'photo' => [$this->getRandomImage(200)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ]
        ]);

        $person2 = $this->createEntry([
            'section' => 'person',
            'type' => 'default',
            'title' => 'Heinz Mustermann',
            'slug' => 'heinz-mustermann',
            'fields' => [
                'firstName' => 'Heinz',
                'lastName' => 'Mustermann',
                'tagline' => 'Dummy',
                'birthday' => new DateTime('1932-11-15'),
                'shortBio' => 'Dummy',
                'filmography' => 'Tbd.',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'photo' => [$this->getRandomImage(200)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ],
        ]);

        $filmSection = $this->createEntry([
            'section' => 'filmSection',
            'type' => 'default',
            'title' => 'New German Cinema',
            'slug' => 'new-german-cinema',
            'fields' => [
                'tagline' => 'The best you can get',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ],
        ]);

        $genre = $this->createEntry([
            'section' => 'genre',
            'type' => 'default',
            'title' => 'Drama',
            'slug' => 'drama'
        ]);

        $country1 = $this->createEntry([
            'section' => 'country',
            'type' => 'default',
            'title' => 'USA',
            'slug' => 'usa'
        ]);

        $country2 = $this->createEntry([
            'section' => 'country',
            'type' => 'default',
            'title' => 'Germany',
            'slug' => 'germany'
        ]);

        $sponsor = $this->createEntry([
            'section' => 'sponsor',
            'type' => 'default',
            'title' => 'Kreissparkasse Recklinghausen-Süd',
            'slug' => 'kreissparkasse recklinghausen-sued'
        ]);

        $language1 = $this->createEntry([
            'section' => 'language',
            'type' => 'default',
            'title' => 'English',
            'slug' => 'english'
        ]);

        $language2 = $this->createEntry([
            'section' => 'language',
            'type' => 'default',
            'title' => 'German',
            'slug' => 'german'
        ]);

        $language3 = $this->createEntry([
            'section' => 'language',
            'type' => 'default',
            'title' => 'French',
            'slug' => 'french'
        ]);

        $location = $this->createEntry([
            'section' => 'location',
            'type' => 'default',
            'title' => 'Gloria Kleinfinstenich',
            'slug' => 'gloria-kleinfinstenich',
            'fields' => [
                'tagline' => 'Small but beautiful',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ],
        ]);

        $film = $this->createEntry([
            'section' => 'film',
            'type' => 'default',
            'title' => 'Die Mutter aller Filme',
            'slug' => 'die-mutter-aller-filme',
            'fields' => [
                'tagline' => 'You never saw something like this',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'filmPoster' => [$this->getRandomImage(300)->id],
                'genres' => [$genre->id],
                'countries' => [$country1->id, $country2->id],
                'cast' => [$person1->id, $person2->id],
                'script' => [$person2->id],
                'camera' => [$person1->id],
                'director' => [$person2->id],
                'filmSections' => [$filmSection->id],
                'sponsors' => [$sponsor->id],
                'languages' => [$language1->id],
                'subtitleLanguages' => [$language2->id, $language3->id],
                'runtime' => 135,
                'fsk' => 16,
                'releaseYear' => 1989,
                'originalTitle' => 'The mother of all movies',
                'bodyContent' => $this->getBodyContent($faker)
            ],
        ]);

        $screening = $this->createEntry([
            'section' => 'screening',
            'type' => 'default',
            'title' => 'The Mother of All Movies - Gloria Kleinfinstenich - 2022-12-24 19:45',
            'slug' => 'the-mother-of-all-movies-gloria-kleinfinstenich-2022-12-24-19-45',
            'fields' => [
                'films' => [$film->id],
                'locations' => [$location->id],
                'screeningDateTime' => new DateTime('2022-12-24 19:45')
            ],
        ]);

        $screening2 = $this->createEntry([
            'section' => 'screening',
            'type' => 'default',
            'title' => 'The Mother of All Movies - Gloria Kleinfinstenich - 2022-12-31 19:45',
            'slug' => 'the-mother-of-all-movies-gloria-kleinfinstenich-2022-12-31-19-45',
            'fields' => [
                'films' => [$film->id],
                'locations' => [$location->id],
                'screeningDateTime' => new DateTime('2022-12-31 19:45')
            ],
        ]);

        return ExitCode::OK;
    }
}
