<?php

namespace wsydney76\ff\console\controllers;

use DateTime;
use Faker\Factory;
use yii\console\ExitCode;

/**
 * Ff Install controller
 */
class SeedController extends \modules\main\console\controllers\SeedController
{
    public $defaultAction = 'index';

    public function actionIndex(): int
    {

        if (!$this->confirm("Create starter content?")) {
            return ExitCode::OK;
        }

        $faker = Factory::create();

        $photos = $this->getImagesFromFolder('photos/starter/', 100);

        $person1 = $this->createEntry([
            'section' => 'person',
            'type' => 'profile',
            'title' => 'Erna von Klawuppke',
            'slug' => 'erna-von-klawuppke',
            'fields' => [
                'firstName' => 'Erna',
                'nameAffix' => 'von',
                'lastName' => 'Klawuppke',
                'tagline' => 'Shooting Star',
                'birthday' => new DateTime('1986-04-09'),
                'shortBio' => 'Erna studied in Kleinfinstenich and New York.',
                'works' => 'Tbd.',
                'photo' => [$photos[0]->id ?? null],
                'featuredImage' => $this->getRandomImageIds(width: 200),
                'bodyContent' => $this->getTextBlock($faker)
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
            'type' => 'profile',
            'title' => 'Klara Mustermann',
            'slug' => 'klara-mustermann',
            'fields' => [
                'firstName' => 'Klara',
                'lastName' => 'Mustermann',
                'tagline' => 'Dummy',
                'birthday' => new DateTime('1932-11-15'),
                'shortBio' => 'Dummy',
                'works' => 'Tbd.',
                'photo' => [$photos[1]->id ?? null],
                'featuredImage' => $this->getRandomImageIds(width: 200),
                'bodyContent' => $this->getTextBlock($faker)
            ],
        ]);

        $filmSection = $this->createEntry([
            'section' => 'filmSection',
            'type' => 'default',
            'title' => 'New German Cinema',
            'slug' => 'new-german-cinema',
            'fields' => [
                'tagline' => 'The best you can get',
                'featuredImage' => $this->getRandomImageIds(),
                'bodyContent' => $this->getTextBlock($faker)
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
                'featuredImage' => $this->getRandomImageIds(),
                'bodyContent' => $this->getTextBlock($faker)
            ],
        ]);

        $competition = $this->createEntry([
            'section' => 'award',
            'type' => 'default',
            'title' => 'Golden Pineapple',
            'slug' => 'golden-pineapple',
            'fields' => [
                'tagline' => 'The price everybody wants to win',
                'featuredImage' => $this->getRandomImageIds(),
                'jury' => [$person1->id, $person2->id],
                'bodyContent' => $this->getTextBlock($faker)
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
                'featuredImage' => $this->getRandomImageIds(),
                'postalAddress' => $faker->address(),
                'phoneNumber' => $faker->phoneNumber(),
                'email' => $faker->email(),
                'bodyContent' => $this->getTextBlock($faker)
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
                'featuredImage' => $this->getRandomImageIds(),
                'filmPoster' => $this->getRandomImageIds(width: 300),
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
                'bodyContent' => $this->getTextBlock($faker),
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

        $this->createEntry([
            'section' => 'diary',
            'type' => 'default',
            'title' => 'Premiere',
            'slug' => 'premiere',
            'fields' => [
                'tagline' => 'The great opening',
                'featuredImage' => $this->getRandomImageIds(),
                'diaryDate' => new DateTime('2023-04-30'),
                'films' => [$film->id],
                'locations' => [$location->id],
                'persons' => [$person2->id],
                'bodyContent' => [
                    [
                        'type' => 'gallery',
                        'fields' => [
                            'images' => $this->getRandomImageIds(6)
                        ]
                    ]
                ]
            ],
        ]);


        return ExitCode::OK;
    }


    protected function getRandomImageIds(int $count = 1, ?int $width = null): array
    {
        $imageIds = [];
        for ($i = 0; $i < $count; $i++) {
            $image = $this->getRandomImage($width ?? $this->minWidth);
            if ($image) {
                $imageIds[] = $image->id;
            }
        }

        return $imageIds;
    }

    protected function getTextBlock(\Faker\Generator $faker)
    {
        $paragraphs = '';
        foreach ($faker->paragraphs($faker->numberBetween(1, 3)) as $paragraph) {
            $paragraphs .= $paragraph . PHP_EOL . PHP_EOL;
        }

        return [
            [
                'type' => 'text',
                'fields' => [
                    'text' => $paragraphs
                ]
            ]
        ];
    }
}
