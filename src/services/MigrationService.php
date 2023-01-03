<?php

namespace wsydney76\ff\services;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\LineBreak;
use craft\fieldlayoutelements\Tip;
use craft\fieldlayoutelements\TitleField;
use craft\fields\Assets;
use craft\fields\Date;
use craft\fields\Entries;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\fields\Time;
use craft\helpers\Console;
use craft\models\FieldGroup;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Site;
use craft\records\FieldGroup as FieldGroupRecord;
use modules\main\MainModule;
use yii\base\Component;
use function collect;
use function extract;
use function in_array;
use function is_string;
use function strtolower;
use const EXTR_OVERWRITE;

/**
 * Migration Service service
 */
class MigrationService extends Component
{


    protected array $sections = [];
    protected array $fields = [];
    protected $fieldGroup;
    protected $doUpdateFieldlayout = [];


    public function install(): bool
    {
        // Craft::$app->runAction('gc', ['interactive' => false]);

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
        MainModule::getInstance()->content->createEntry([
            'section' => 'page',
            'type' => 'pageTemplate',
            'title' => 'Program',
            'slug' => 'program',
            'parent' => $homePage,
            'fields' => [
                'pageTemplate' => '@ff/partials/program.twig',
            ],
            'localized' => [
                'de' => [
                    'title' => 'Programm',
                    'slug' => 'programm',
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
            $this->message("Field group created");
        }

        return true;
    }

    private function createSections()
    {
        return (

            $this->createSection([
                'name' => 'Film',
                'plural' => 'Films',
                'addIndexPage' => true,
                'createEntriesField' => true,
                'template' => 'ff/layouts/sidebar'
            ]) &&

            $this->createSection([
                'name' => 'Person',
                'plural' => 'People',
                'titleFormat' => '{firstName} {nameAffix} {lastName}',
                'addIndexPage' => true,
                'template' => 'ff/layouts/sidebar'
            ]) &&

            $this->createSection([
                'type' => Section::TYPE_STRUCTURE,
                'name' => 'Section',
                'plural' => 'Sections',
                'handle' => 'filmSection',
                'addIndexPage' => true,
                'createEntriesField' => true,
                'entriesFieldHandle' => 'filmSections',
                'template' => 'ff/layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Location',
                'plural' => 'Locations',
                'addIndexPage' => true,
                'createEntriesField' => true,
                'template' => 'ff/layouts/sidebar'
            ]) &&

            $this->createSection([
                'name' => 'Language',
                'plural' => 'Languages',
                'addIndexPage' => false,
                'createEntriesField' => true,
                'template' => 'ff/layouts/md'
            ]) &&

            $this->createSection([
                'type' => Section::TYPE_STRUCTURE,
                'name' => 'Competition',
                'plural' => 'Competitions',
                'addIndexPage' => true,
                'createEntriesField' => true,
                'template' => 'ff/layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Country',
                'plural' => 'Countries',
                'createEntriesField' => true,
                'template' => 'ff/layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Genre',
                'plural' => 'Genres',
                'createEntriesField' => true,
                'template' => 'ff/layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Diary',
                'plural' => 'Diaries',
                'addIndexPage' => true,
                'template' => 'ff/layouts/sidebar'
            ]) &&


            $this->createSection([
                'name' => 'Sponsor',
                'plural' => 'Sponsors',
                'createEntriesField' => true,
                'template' => 'ff/layouts/md'
            ]) &&

            $this->createSection([
                'type' => Section::TYPE_STRUCTURE,
                'name' => 'Topic',
                'plural' => 'Topics',
                'addIndexPage' => true,
                'createEntriesField' => true,
                'template' => 'ff/layouts/md'
            ]) &&

            $this->createSection([
                'name' => 'Screening',
                'plural' => 'screenings',
                'titleFormat' => '{films.one.title} - {locations.one.title} - {screeningDate|date(\'Y-m-d\')}:{screeningTime|time(\'H:i\')}',
                'template' => 'ff/layouts/md'
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
        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Cast',
            'handle' => 'cast',
            'sources' => [
                "section:$section->uid"
            ],
            'selectionLabel' => 'Add a person',
            'searchable' => true
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
            'searchable' => true
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
            'searchable' => true
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
            'searchable' => true
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
            'searchable' => true
        ]);

        $this->createField([
            'class' => Entries::class,
            'groupId' => $fieldGroup->id,
            'name' => 'People',
            'handle' => 'people',
            'sources' => [
                "section:$section->uid"
            ],
            'selectionLabel' => 'Add a person',
            'searchable' => true
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
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'firstName',
            'name' => 'First Name',
            'charLimit' => 30,
            'searchable' => true
        ]);

        $this->createField([
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'nameAffix',
            'name' => 'Name affix',
            'charLimit' => 30,
            'searchable' => true
        ]);

        $this->createField([
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'lastName',
            'name' => 'Last Name',
            'charLimit' => 30,
            'searchable' => true
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
            'handle' => 'photo',
            'name' => 'Photo',
            'defaultUploadLocationSource' => "volume:$volume->uid",
            'defaultUploadLocationSubpath' => "photos",
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
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'shortBio',
            'name' => 'Short Biography',
            'multiline' => true,
            'initialRows' => 2,
            'translationMethod' => Field::TRANSLATION_METHOD_LANGUAGE,
            'searchable' => true
        ]);

        $this->createField([
            'class' => PlainText::class,
            'groupId' => $fieldGroup->id,
            'handle' => 'filmography',
            'name' => 'Filmography',
            'multiline' => true,
            'initialRows' => 2,
            'translationMethod' => Field::TRANSLATION_METHOD_LANGUAGE,
            'searchable' => true
        ]);

        $this->createField([
            'class' => Date::class,
            'groupId' => $fieldGroup->id,
            'name' => 'Birthday',
            'handle' => 'birthday'
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

        return true;
    }

    protected function updateFieldLayouts()
    {
        $this->updateFieldLayout('film', [
            'Content' => [
                'originalTitle',
                ['featuredImage', ['width' => 25, 'required' => true]], ['filmPoster', ['width' => 25]],
                'tagline',
                ['filmSections', ['width' => 25]], ['competitions', ['width' => 25]],
                ['topics', ['width' => 25]], ['sponsors', ['width' => 25]],
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


        $this->updateFieldLayout('person', [
            ['firstName', ['required' => true, 'width' => 25]],
            ['nameAffix', ['width' => 25]],
            ['lastName', ['required' => true, 'width' => 25]],
            'featuredImage', 'tagline',
            'photo', 'birthday', 'shortBio', 'filmography',
            'bodyContent'
        ]);

        $this->updateFieldLayout('filmSection', [
            'heroArea', 'featuredImage', 'tagline', 'bodyContent'
        ]);

        $this->updateFieldLayout('location', [
            'featuredImage', 'tagline',
            'postalAddress', 'phoneNumber', 'email',
            'bodyContent'
        ]);

        $this->updateFieldLayout('competition', [
            'heroArea', 'featuredImage', 'tagline', 'jury', 'bodyContent'
        ]);

        $this->updateFieldLayout('diary', [
            'featuredImage', 'tagline',
            ['diaryDate', ['required' => true]],
            ['locations', ['width' => 25]],
            ['films', ['width' => 25]],
            ['people', ['width' => 25]],
            'bodyContent'
        ]);

        $this->updateFieldLayout('sponsor', [
            'heroArea', 'featuredImage', 'tagline', 'bodyContent'
        ]);

        $this->updateFieldLayout('topic', [
            'heroArea', 'featuredImage', 'tagline', 'bodyContent'
        ]);

        $this->updateFieldLayout('language', [
            ['abbreviation', ['required' => true]]
        ]);

        $this->updateFieldLayout('screening', [
            'films', 'locations',
            ['screeningDate', ['width' => 25, 'required' => true]],
            ['screeningTime', ['width' => 25, 'required' => true]],
        ]);

        return true;
    }

    private function updateElementSources(): bool
    {
        $fields = Craft::$app->fields;

        $defaultFestivalSections = ['filmSection', 'competition', 'sponsor', 'diary'];
        $categorySections = ['topic', 'country', 'genre'];

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

        $this->updateElementSource('Festival', 'person', [
            "field:{$fields->getFieldByHandle('tagline')->uid}",
            "field:{$fields->getFieldByHandle('photo')->uid}",
            ...$defaultTableAttributes
        ]);

        $this->updateElementSource('Festival', 'location', [
            "field:{$fields->getFieldByHandle('tagline')->uid}",
            "field:{$fields->getFieldByHandle('postalAddress')->uid}",
            ...$defaultTableAttributes
        ]);

        $this->updateElementSource('Festival', 'screening', [
            "field:{$fields->getFieldByHandle('films')->uid}",
            "field:{$fields->getFieldByHandle('locations')->uid}",
            "field:{$fields->getFieldByHandle('screeningDate')->uid}",
            "field:{$fields->getFieldByHandle('screeningTime')->uid}",
            ...$defaultTableAttributes
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

    private function createSection(array $config): bool
    {
        $name = $config['name'];
        $type = $config['type'] ?? Section::TYPE_CHANNEL;
        $handle = $config['handle'] ?? strtolower($config['name']);
        $plural = $config['plural'] ?? $config['name'];
        $baseUri = $config['baseUri'] ?? strtolower($plural);
        $titleFormat = $config['titleFormat'] ?? '';
        $addIndexPage = $config['addIndexPage'] ?? false;
        $createEntriesField = $config['createEntriesField'] ?? false;
        $template = $config['template'] ?? "ff/sections/$handle";

        $this->sections[$handle] = Craft::$app->sections->getSectionByHandle($handle);
        if ($this->sections[$handle]) {
            return true;
        }

        $section = new Section([
                'name' => $name,
                'handle' => $handle,
                'type' => $type,
                'siteSettings' => collect(Craft::$app->sites->getAllSites())
                    ->map(fn(Site $site) => new Section_SiteSettings([
                        'siteId' => $site->id,
                        'enabledByDefault' => true,
                        'hasUrls' => true,
                        'uriFormat' => Craft::t('ff-light', $baseUri, language: $site->language) . '/{slug}',
                        'template' => $template
                    ]))
                    ->toArray()
            ]
        );

        if (!Craft::$app->sections->saveSection($section)) {
            $this->logError("Could not create section $handle: {$section}", $section);
            return false;
        }

        $this->message("Section $name created.");

        $this->doUpdateFieldlayout[] = $section->handle;

        $type = $section->getEntryTypes()[0];
        $type->titleTranslationMethod = Field::TRANSLATION_METHOD_LANGUAGE;
        if ($titleFormat) {
            $type->hasTitleField = false;
            $type->titleFormat = $titleFormat;
        }

        if (!Craft::$app->sections->saveEntryType($type)) {
            $this->logError("Could not save entry type for $handle", $type);
        }

        $this->message("Entry type $section->name/$type->name updated.");

        if ($addIndexPage) {
            $homePage = Entry::findOne(['slug' => '__home__']);

            MainModule::getInstance()->content->createEntry([
                'section' => 'page',
                'type' => 'pageTemplate',
                'title' => $plural,
                'slug' => $baseUri,
                'parent' => $homePage,
                'fields' => [
                    'pageTemplate' => "@ff/sections/$section->handle/index"
                ],
                'localized' => [
                    'de' => [
                        'title' => Craft::t('ff-light', $plural, language: 'de_DE'),
                        'slug' => Craft::t('ff-light', $baseUri, language: 'de_DE'),
                    ]
                ]
            ]);
        }

        if ($createEntriesField) {
            $this->createField([
                'class' => Entries::class,
                'groupId' => $this->fieldGroup->id,
                'name' => $plural,
                'handle' => $config['entriesFieldHandle'] ?? $baseUri,
                'sources' => [
                    "section:$section->uid"
                ]
            ]);
        }

        $this->sections[$handle] = $section;
        return true;
    }


    protected function createField(array $config)
    {

        extract($config, EXTR_OVERWRITE);

        // Create package field
        $this->fields[$handle] = Craft::$app->fields->getFieldByHandle($handle);
        if ($this->fields[$handle]) {
            return true;
        }

        $field = Craft::createObject($config);

        if (!Craft::$app->fields->saveField($field)) {
            $this->logError("Could not save field {$handle}", $field);
            return false;
        }

        $this->fields[$handle] = $field;

        $this->message("Field {$name} created.");
        return true;
    }


    protected function updateFieldLayout(string $sectionHandle, array $tabConfigs)
    {
        // Only create field layout for newly created sections
        if (!in_array($sectionHandle, $this->doUpdateFieldlayout)) {
            return true;
        }

        // Single tab
        if (isset($tabConfigs[0])) {
            $tabConfigs = [
                'Content' => $tabConfigs
            ];
        };


        /** @var Section $section */
        $section = $this->sections[$sectionHandle];
        $layout = $section->entryTypes[0]->getFieldLayout();
        $tab = $layout->getTabs()[0];

        $tabs = [];

//        foreach ($tab->getElements() as $element) {
//            if ($element instanceof CustomField) {
//                /** @var CustomField $element */
//                if ($element->getField()->handle === $layoutElements[0][0]) {
//                    $this->message("Layout for $sectionHandle exists");
//                    return true;
//                }
//            }
//        }

        $sortOrder = 1;
        foreach ($tabConfigs as $label => $layoutElements) {
            $tab = new FieldLayoutTab();
            $tab->name = $label;
            $tab->sortOrder = $sortOrder++;
            $tab->layout = $layout;


            $tab->setElements(collect($layoutElements)
                ->map(function($layoutElement) {
                    if (is_string($layoutElement)) {
                        $layoutElement = [$layoutElement, []];
                    }

                    if ($layoutElement[0] === 'titleField') {
                        return new TitleField();
                    }

                    if ($layoutElement[0] === 'lineBreak') {
                        return new LineBreak();
                    }

                    if ($layoutElement[0] === 'tip') {
                        return new Tip($layoutElement[1]);
                    }

                    return new CustomField(Craft::$app->fields->getFieldByHandle($layoutElement[0]), $layoutElement[1]);
                })
                ->toArray()
            );


            $tabs[] = $tab;
        }

        $layout->setTabs($tabs);

        if (!Craft::$app->fields->saveLayout($layout)) {

            $this->logError("Could not save fieldlayout for $sectionHandle", $layout);
            return false;
        }

        $this->message("Field layout for $sectionHandle created.");

        return true;
    }

    private function updateElementSource(string $heading, string $sectionHandle, array $tableAttributes)
    {
        $config = Craft::$app->projectConfig->get('elementSources');
        $section = Craft::$app->sections->getSectionByHandle($sectionHandle);
        $key = "section:{$section->uid}";

        // Check for existing source
        foreach ($config['craft\\elements\\Entry'] as $source) {
            if ($source['type'] === 'native' && $source['key'] === $key) {
               return;
            }
        }

        // Ensure a heading is set
        $headingExists = false;
        foreach ($config['craft\\elements\\Entry'] as $source) {
            if ($source['type'] === 'heading' && $source['heading'] === $heading) {
                $headingExists = true;
                break;
            }
        }

        // Append heading
        if (!$headingExists) {
            $config['craft\\elements\\Entry'][] = [
                'heading' => $heading,
                'type' => 'heading'
            ];
        }

        // A source for newly created sections does not exist, so we can just append it.
        $config['craft\\elements\\Entry'][] = [
            'disabled' => false,
            'key' => $key,
            'tableAttributes' => $tableAttributes,
            'type' => 'native'
        ];

        Craft::$app->projectConfig->set('elementSources', $config);
        $this->message("ElementSource for $sectionHandle updated.");
    }



    private function getFieldGroup(string $fieldGroup)
    {
        return FieldGroupRecord::findOne(['name' => $fieldGroup]);
    }

    protected function message($message)
    {
        if (Craft::$app->request->isConsoleRequest) {
            Console::output($message);
        } else {
            Craft::info($message, 'ff/install');
        }
    }

    protected function logError(string $message, ?Model $model = null)
    {
        Craft::error($message, 'ff/install');
        $this->message($message);
        if ($model) {
            Craft::error($model->errors, 'ff/install');
        }
    }

}
