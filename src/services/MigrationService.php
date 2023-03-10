<?php

namespace wsydney76\ff\services;

use Craft;
use craft\base\Field;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\conditions\entries\TypeConditionRule;
use craft\elements\Entry;
use craft\fields\Assets;
use craft\fields\Date;
use craft\fields\Entries;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\fields\Time;
use craft\fields\Url;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\models\FieldGroup;
use craft\models\Section;
use Faker\Factory;
use modules\base\BaseModule;
use modules\base\services\BaseMigrationService;

/**
 * Migration Service service
 */
class MigrationService extends BaseMigrationService
{

    public string $logCategory = '@ff/_install';
    protected string $translationCategory = 'ff-light';
    protected string $templateRoot = '@ff';


    public function install(): bool
    {
        // Craft::$app->runAction('gc', ['interactive' => false]);

        $this->faker = Factory::create();

        $this->createPages() &&
        $this->createFieldGroup() &&
        $this->createSections() &&
        $this->createFields() &&
        $this->updateFieldLayouts() &&
        $this->updateElementSources();

        return true;
    }


    private function createPages()
    {
        $homePage = Entry::findOne(['slug' => '__home__']);
        BaseModule::getInstance()->contentService->createEntry([
            'section' => 'page',
            'type' => 'pageTemplate',
            'title' => 'Program',
            'slug' => 'program',
            'site' => 'en',
            'parent' => $homePage,
            'fields' => [
                'pageTemplate' => '@ff/_partials/program.twig',
            ],
            'localized' => [
                'de' => [
                    'title' => 'Programm',
                    'slug' => 'programm',
                ]
            ]
        ]);

        BaseModule::getInstance()->contentService->createEntry([
            'section' => 'page',
            'type' => 'pageTemplate',
            'title' => 'People',
            'slug' => 'people',
            'site' => 'en',
            'parent' => $homePage,
            'fields' => [
                'pageTemplate' => '@ff/_sections/person/index.twig',
            ],
            'localized' => [
                'de' => [
                    'title' => 'Leute',
                    'slug' => 'leute',
                ]
            ]
        ]);


        return true;
    }

    private function createFieldGroup()
    {
        $this->fieldGroup = $this->getFieldGroup('Festival');
        if (!$this->fieldGroup) {
            $this->fieldGroup = new FieldGroup([
                'name' => 'Festival',
            ]);

            if (!Craft::$app->fields->saveGroup($this->fieldGroup)) {
                $this->logError('Could not install fieldGroup', $this->fieldGroup);
                return false;
            }
            $this->info("Field group created");
        }

        return true;
    }

    private function createSections()
    {
        return (

            $this->createSection([
                'name' => 'Diary',
                'plural' => 'Diary',
                'addIndexPage' => true,
                'withHeroFields' => true,
                'template' => '@ff/_layouts/sidebar'
            ]) &&

            $this->createSection([
                'type' => Section::TYPE_STRUCTURE,
                'name' => 'Award',
                'plural' => 'Awards',
                'addIndexPage' => true,
                'withHeroFields' => true,
                'template' => '@ff/_layouts/md'
            ]) &&

            $this->createSection([
                'type' => Section::TYPE_STRUCTURE,
                'name' => 'Section',
                'plural' => 'Sections',
                'handle' => 'filmSection',
                'addIndexPage' => true,
                'withHeroFields' => true,
                'createEntriesField' => true,
                'entriesFieldHandle' => 'filmSections',
                'template' => '@ff/_layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Film',
                'plural' => 'Films',
                'addIndexPage' => true,
                'createEntriesField' => true,
                'template' => '@ff/_layouts/sidebar'
            ]) &&


            $this->createSection([
                'name' => 'Location',
                'plural' => 'Locations',
                'addIndexPage' => true,
                'createEntriesField' => true,
                'template' => '@ff/_layouts/sidebar'
            ]) &&

            $this->createSection([
                'name' => 'Language',
                'plural' => 'Languages',
                'addIndexPage' => false,
                'createEntriesField' => true,
                'template' => '@ff/_layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Country',
                'plural' => 'Countries',
                'createEntriesField' => true,
                'template' => '@ff/_layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Genre',
                'plural' => 'Genres',
                'createEntriesField' => true,
                'template' => '@ff/_layouts/md'
            ]) &&


            $this->createSection([
                'name' => 'Sponsor',
                'plural' => 'Sponsors',
                'createEntriesField' => true,
                'template' => '@ff/_layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Advertisement',
                'plural' => 'Advertisements',
                'template' => '@ff/_layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Screening',
                'plural' => 'screenings',
                'titleFormat' => '{films.one.status(null).drafts(null).provisionalDrafts(null).title} - {locations.status(null).drafts(null).provisionalDrafts(null).one.title} - {screeningDate|date(\'Y-m-d\')}:{screeningTime|time(\'H:i\')}',
                'hasUrls' => false,
                'template' => '@ff/_layouts/md'
            ])
        );
    }

    protected function createFields()
    {
        $fieldGroup = $this->getFieldGroup('Festival');
        if (!$fieldGroup) {
            return false;
        }

        $section = Craft::$app->sections->getSectionByHandle('person');
        $profileType = ArrayHelper::firstWhere($section->entryTypes, 'handle', 'profile');

        $selectionCondition = [
            'elementType' => Entry::class,
            'fieldContext' => 'global',
            'class' => EntryCondition::class,
            'conditionRules' => [
                [
                    'class' => TypeConditionRule::class,
                    'operator' => 'in',
                    'values' => [
                        $profileType->uid
                    ]
                ]
            ]
        ];

        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Cast',
            'handle' => 'cast',
            'sources' => [
                "section:$section->uid"
            ],
            'selectionLabel' => 'Add a person',
            'searchable' => true,
            'selectionCondition' => $selectionCondition
        ]);

        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Script',
            'handle' => 'script',
            'sources' => [
                "section:$section->uid"
            ],
            'selectionLabel' => 'Add a person',
            'searchable' => true,
            'selectionCondition' => $selectionCondition
        ]);

        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Camera',
            'handle' => 'camera',
            'sources' => [
                "section:$section->uid"
            ],
            'selectionLabel' => 'Add a person',
            'searchable' => true,
            'selectionCondition' => $selectionCondition
        ]);

        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Director',
            'handle' => 'director',
            'sources' => [
                "section:$section->uid"
            ],
            'selectionLabel' => 'Add a person',
            'searchable' => true,
            'selectionCondition' => $selectionCondition
        ]);

        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Jury',
            'handle' => 'jury',
            'sources' => [
                "section:$section->uid"
            ],
            'selectionLabel' => 'Add a person',
            'searchable' => true,
            'selectionCondition' => $selectionCondition
        ]);


        $this->createField([
            'class' => Number::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'releaseYear',
            'name' => 'Release Year',
            'previewFormat' => Number::FORMAT_NONE,
            'searchable' => true
        ]);


        $languageSection = Craft::$app->sections->getSectionByHandle('language');
        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Subtitle Languages',
            'handle' => 'subtitleLanguages',
            'sources' => [
                "section:$languageSection->uid"
            ],
            'selectionLabel' => 'Add language'
        ]);

        $this->createField([
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Abbreviation',
            'handle' => 'abbreviation',
            'charLimit' => 3
        ]);


        $this->createField([
            'class' => Number::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'fsk',
            'name' => 'FSK'
        ]);

        $this->createField([
            'class' => Number::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'runtime',
            'name' => 'Runtime',
            'min' => 5,
            'max' => 600,
            'suffix' => 'minutes',
            'decimals' => 0,
        ]);

        $this->createField([
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'originalTitle',
            'name' => 'Original title',
            'searchable' => true
        ]);

        $volume = Craft::$app->volumes->getVolumeByHandle('images');

        $this->createField([
            'class' => Assets::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'filmPoster',
            'name' => 'Film Poster',
            'defaultUploadLocationSource' => "volume:$volume->uid",
            'defaultUploadLocationSubpath' => "posters",
            'maxRelations' => 1,
            'selectionLabel' => 'Select image',
            'viewMode' => 'large',
            'sources' => [
                "volume:$volume->uid"
            ]
        ]);

        $this->createField([
            'class' => Assets::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'adImage',
            'name' => 'Advertisement Image',
            'defaultUploadLocationSource' => "volume:$volume->uid",
            'defaultUploadLocationSubpath' => "advertisements",
            'maxRelations' => 1,
            'selectionLabel' => 'Select image',
            'viewMode' => 'large',
            'sources' => [
                "volume:$volume->uid"
            ]
        ]);


        $this->createField([
            'class' => Date::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'diaryDate',
            'name' => 'Diary Date',
            'showDate' => true,
            'showTime' => false
        ]);

        $this->createField([
            'class' => Date::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'screeningDate',
            'name' => 'Screening Date',
            'showDate' => true,
            'showTime' => false
        ]);

        $this->createField([
            'class' => Time::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'screeningTime',
            'name' => 'Screening Time',
            'minuteIncrement' => 30
        ]);

        $this->createField([
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'remarks',
            'name' => 'Remarks',
            'translationMethod' => Field::TRANSLATION_METHOD_LANGUAGE,
            'charLimit' => 80
        ]);

        $this->createField([
            'class' => Url::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'ticketLink',
            'name' => 'Ticket Link',
            'translationMethod' => Field::TRANSLATION_METHOD_NONE,
        ]);

        $filmSection = Craft::$app->sections->getSectionByHandle('film');
        $personSection = Craft::$app->sections->getSectionByHandle('person');

        $profileType = ArrayHelper::firstWhere($personSection->entryTypes, 'handle', 'profile');
        $filmType = $filmSection->entryTypes[0];

        $selectionCondition = [
            'elementType' => Entry::class,
            'fieldContext' => 'global',
            'class' => EntryCondition::class,
            'conditionRules' => [
                [
                    'class' => TypeConditionRule::class,
                    'operator' => 'in',
                    'values' => [
                        $profileType->uid,
                        $filmType->uid
                    ]
                ]
            ]
        ];

        $this->createMatrixField([
            'groupId' => $fieldGroup->id,
            'handle' => 'awards',
            'name' => 'Awards',
            'searchable' => true,
            'blocktypes' => [
                [
                    'name' => 'Award',
                    'handle' => 'award',
                    'fields' => [
                        [
                            'class' => PlainText::class,
                            'handle' => 'description',
                            'name' => 'Description',
                            'translationMethod' => Field::TRANSLATION_METHOD_LANGUAGE,
                            'charLimit' => 80,
                            'searchable' => true,
                            'layoutConfig' => [
                                'required' => true,
                                'width' => 25,
                            ]
                        ],
                        [
                            'class' => Entries::class,
                            'handle' => 'winner',
                            'name' => 'Winner',
                            'sources' => [
                                "section:$filmSection->uid",
                                "section:$personSection->uid",
                            ],
                            'selectionLabel' => 'Add a film/person',
                            'searchable' => true,
                            'selectionCondition' => $selectionCondition,
                            'layoutConfig' => [
                                'width' => 25,
                            ]
                        ],
                        [
                            'class' => Entries::class,
                            'handle' => 'nominees',
                            'name' => 'Nominees',
                            'sources' => [
                                "section:$filmSection->uid",
                                "section:$personSection->uid",
                            ],
                            'selectionLabel' => 'Add a film/person',
                            'searchable' => true,
                            'selectionCondition' => $selectionCondition,
                            'layoutConfig' => [
                                'width' => 25,
                            ]
                        ],
                    ]
                ]
            ]
        ]);

        return true;
    }

    protected function updateFieldLayouts()
    {
        $this->updateFieldLayout('film', [
            'Content' => [
                'originalTitle',
                ['featuredImage', ['width' => 25, 'required' => true]], ['filmPoster', ['width' => 25]],
                'tagline',
                ['filmSections', ['width' => 25]],
                ['sponsors', ['width' => 25]],
                'bodyContent'
            ],
            'Details' => [
                ['fsk', ['width' => 25]], ['runtime', ['width' => 25]],
                ['releaseYear', ['width' => 25]],
                'lineBreak',
                ['genres', ['width' => 25]], ['countries', ['width' => 25]],
                ['languages', ['width' => 25]], ['subtitleLanguages', ['width' => 25]],
            ],

            'Crew' => [
                'cast', 'script', 'camera', 'director',
            ]
        ]);


//        $this->updateFieldLayout('person', [
//            ['firstName', ['required' => true, 'width' => 25]],
//            ['nameAffix', ['width' => 25]],
//            ['lastName', ['required' => true, 'width' => 25]],
//            'featuredImage', 'tagline',
//            'photo', 'birthday', 'shortBio', 'works',
//            'bodyContent'
//        ]);

        $this->updateFieldLayout('filmSection', [
            'heroArea', 'featuredImage', 'tagline', 'bodyContent'
        ]);

        $this->updateFieldLayout('location', [
            'featuredImage', 'tagline',
            'postalAddress', 'phoneNumber', 'email',
            'bodyContent'
        ]);

        $this->updateFieldLayout('award', [
            'heroArea', 'featuredImage', 'tagline', 'jury', 'bodyContent', 'awards'
        ]);

        $this->updateFieldLayout('diary', [
            'featuredImage', 'tagline',
            ['diaryDate', ['required' => true]],
            ['locations', ['width' => 25]],
            ['films', ['width' => 25]],
            ['persons', ['width' => 25]],
            'bodyContent'
        ]);

        $this->updateFieldLayout('sponsor', [
            'heroArea', 'featuredImage', 'tagline', 'bodyContent'
        ]);

        $this->updateFieldLayout('advertisement', [
            'adImage', 'filmSections', 'films', 'locations', 'persons'
        ]);

        $this->updateFieldLayout('language', [
            ['abbreviation', ['required' => true]]
        ]);

        $this->updateFieldLayout('screening', [
            'films', 'locations',
            ['screeningDate', ['width' => 25, 'required' => true]],
            ['screeningTime', ['width' => 25, 'required' => true]],
            'remarks', 'ticketLink'
        ]);

        return true;
    }

    private function updateElementSources(): bool
    {
        $fields = Craft::$app->fields;

        $defaultFestivalSections = ['filmSection', 'award', 'sponsor', 'diary', 'advertisement'];
        $categorySections = ['country', 'genre'];

        $defaultTableAttributes = [
            'author',
            'dateCreated',
            'link'
        ];

        $this->updateElementSource('Festival', 'film', [
            "field:{$fields->getFieldByHandle('tagline')->uid}",
            "field:{$fields->getFieldByHandle('filmSections')->uid}",
            ...$defaultTableAttributes
        ]);

//        $this->updateElementSource('Festival', 'person', [
//            "field:{$fields->getFieldByHandle('tagline')->uid}",
//            "field:{$fields->getFieldByHandle('photo')->uid}",
//            ...$defaultTableAttributes
//        ]);

        $this->updateElementSource('Festival', 'location', [
            "field:{$fields->getFieldByHandle('tagline')->uid}",
            "field:{$fields->getFieldByHandle('postalAddress')->uid}",
            ...$defaultTableAttributes
        ]);

        $this->updateElementSource('Festival', 'screening', [
            "field:{$fields->getFieldByHandle('remarks')->uid}",
            'author',
            'dateCreated'
        ]);

        foreach ($defaultFestivalSections as $section) {
            $this->updateElementSource('Festival', $section, $defaultTableAttributes);
        }

        foreach ($categorySections as $section) {
            $this->updateElementSource('Categories', $section, $defaultTableAttributes);
        }

        $this->updateElementSource('Categories', 'language', [
            "field:{$fields->getFieldByHandle('abbreviation')->uid}",
            ...$defaultTableAttributes
        ]);

        return true;
    }

}
