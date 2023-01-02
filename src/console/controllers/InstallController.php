<?php

namespace wsydney76\ff\console\controllers;

use Craft;
use craft\console\Controller;
use craft\helpers\Console;
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
            'title' => 'Erna von Klawuppke',
            'slug' => 'erna-von-klawuppke',
            'fields' => [
                'firstName' => 'Erna',
                'nameAffix' => 'von',
                'lastName' => 'Klawuppke',
                'tagline' => 'Shooting Star',
                'birthday' => new DateTime('1986-04-09'),
                'shortBio' => 'Erna studied in Kleinfinstenich and New York.',
                'filmography' => 'Tbd.',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'photo' => [$this->getRandomImage(200)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ],
            'localized' => [
                'de' => [
                    'fields' => [
                        'tagline' => 'Senkrechtstarterin',
                        'shortBio' => 'Erna studierte in Kleinfinstenich und New York.',
                    ]
                ]
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
            'localized' => [
                'de' => [
                    'title' => 'Neues Deutsches Kino',
                    'slug' => 'neues-deutsches-kino',
                    'fields' => [
                        'tagline' => 'Besser wirds nicht.'
                    ]
                ]
            ]
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
            'slug' => 'germany',
            'localized' => [
                'de' => [
                    'title' => 'Deutschland',
                    'slug' => 'deutschland'
                ]
            ]
        ]);

        $sponsor = $this->createEntry([
            'section' => 'sponsor',
            'type' => 'default',
            'title' => 'Kreissparkasse Recklinghausen-Süd',
            'slug' => 'kreissparkasse-recklinghausen-sued',
            'fields' => [
                'tagline' => 'Our Solution - Your problem',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ],
        ]);

        $competition = $this->createEntry([
            'section' => 'competition',
            'type' => 'default',
            'title' => 'Golden Pineapple',
            'slug' => 'golden-pineapple',
            'fields' => [
                'tagline' => 'The price everybody wants to win',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ],
            'localized' => [
                'de' => [
                    'title' => 'Goldene Ananas',
                    'slug' => 'goldene-ananas',
                    'fields' => [
                        'tagline' => 'Diesen Preis wollen alle gewinnen.'
                    ]
                ]
            ]
        ]);

        $topic = $this->createEntry([
            'section' => 'topic',
            'type' => 'default',
            'title' => 'Super Heroes',
            'slug' => 'super-heroes',
            'fields' => [
                'tagline' => 'Everybody can become a super hero',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'bodyContent' => $this->getBodyContent($faker)
            ],
            'localized' => [
                'de' => [
                    'title' => 'Superhelden',
                    'slug' => 'superhelden',
                    'fields' => [
                        'tagline' => 'Jeder kann ein Superheld werden.'
                    ]
                ]
            ]
        ]);

        $language1 = $this->createEntry([
            'section' => 'language',
            'type' => 'default',
            'title' => 'English',
            'slug' => 'english',
            'fields' => [
                'abbreviation' => 'en',
            ],
            'localized' => [
                'de' => [
                    'title' => 'Englisch',
                    'slug' => 'englisch'
                ]
            ]
        ]);

        $language2 = $this->createEntry([
            'section' => 'language',
            'type' => 'default',
            'title' => 'German',
            'slug' => 'german',
            'fields' => [
                'abbreviation' => 'de',
            ],
            'localized' => [
                'de' => [
                    'title' => 'Deutsch',
                    'slug' => 'deutsch'
                ]
            ]
        ]);

        $language3 = $this->createEntry([
            'section' => 'language',
            'type' => 'default',
            'title' => 'French',
            'slug' => 'french',
            'fields' => [
                'abbreviation' => 'fr',
            ],
            'localized' => [
                'de' => [
                    'title' => 'Französisch',
                    'slug' => 'französisch'
                ]
            ]
        ]);

        $location = $this->createEntry([
            'section' => 'location',
            'type' => 'default',
            'title' => 'Gloria Kleinfinstenich',
            'slug' => 'gloria-kleinfinstenich',
            'fields' => [
                'tagline' => 'Small but beautiful',
                'featuredImage' => [$this->getRandomImage($this->minWidth)->id],
                'postalAddress' => $faker->address(),
                'phoneNumber' => $faker->phoneNumber(),
                'email' => $faker->email(),
                'bodyContent' => $this->getBodyContent($faker)
            ],
            'localized' => [
                'de' => [
                    'fields' => [
                        'tagline' => 'Klein aber fein'
                    ]
                ]
            ]
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
                'competitions' => [$competition->id],
                'topics' => [$topic->id],
                'sponsors' => [$sponsor->id],
                'languages' => [$language1->id],
                'subtitleLanguages' => [$language2->id, $language3->id],
                'runtime' => 135,
                'fsk' => 16,
                'releaseYear' => 1989,
                'originalTitle' => 'The mother of all movies',
                'bodyContent' => $this->getBodyContent($faker),
            ],
            'localized' => [
                'de' => [
                    'fields' => [
                        'tagline' => 'So etwas haben Sie noch nie gesehen.'
                    ]
                ]
            ]
        ]);

        $screening = $this->createEntry([
            'section' => 'screening',
            'type' => 'default',
            'title' => '',
            'slug' => 'screening1',
            'fields' => [
                'films' => [$film->id],
                'locations' => [$location->id],
                'screeningDate' => new DateTime('2023-04-24'),
                'screeningTime' => new DateTime('16:00')
            ],
        ]);

        $screening2 = $this->createEntry([
            'section' => 'screening',
            'type' => 'default',
            'title' => '',
            'slug' => 'screening2',
            'fields' => [
                'films' => [$film->id],
                'locations' => [$location->id],
                'screeningDate' => new DateTime('2023-04-30'),
                'screeningTime' => new DateTime('19:45')
            ],
        ]);

        return ExitCode::OK;
    }

    public function actionTest(): int
    {

        $config = Craft::$app->projectConfig->get('elementSources');
        $section = Craft::$app->sections->getSectionByHandle('film');
        $field = Craft::$app->fields->getFieldByHandle('cast');
        Console::output($section->uid);

        $config['craft\\elements\\Entry'][] = [
            'disabled' => false,
            'key' => "section:$section->uid",
            'tableAttributes' => [
                "field:$field->uid",
                'slug',
                'postDate',
                'link'
            ],
            'type' => 'native'
        ];

        Craft::$app->projectConfig->set('elementSources', $config);

//        foreach ($config as $type => $sources) {
//            if ($type === 'craft\\elements\\Entry') {
//                foreach ($sources as  $key => $source) {
//                    if ($source['type'] === 'native' && $source['key'] === "section:$section->uid") {
//                        $config[$type][$key]['tableAttributes'] = [
//                          'slug'
//                        ];
//                    }
//                }
//            }
//        }

        return ExitCode::OK;
    }
}
