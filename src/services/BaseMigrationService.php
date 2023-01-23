<?php

namespace wsydney76\ff\services;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\LineBreak;
use craft\fieldlayoutelements\Tip;
use craft\fieldlayoutelements\TitleField;
use craft\fields\Entries;
use craft\fields\Matrix;
use craft\helpers\Console;
use craft\models\FieldLayoutTab;
use craft\models\MatrixBlockType;
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
 * Base Migration Service service
 */
class BaseMigrationService extends Component
{
    protected string $logCategory = 'migration';
    protected string $imageVolume = 'images';
    protected int $minHeroImageWidth = 1900;

    protected array $sections = [];
    protected array $fields = [];
    protected $fieldGroup;
    protected $doUpdateFieldlayout = [];

    protected $faker;


    protected function createSection(array $config): bool
    {
        $name = $config['name'];
        $type = $config['type'] ?? Section::TYPE_CHANNEL;
        $handle = $config['handle'] ?? strtolower($config['name']);
        $plural = $config['plural'] ?? $config['name'];
        $baseUri = $config['baseUri'] ?? strtolower($plural);
        $titleFormat = $config['titleFormat'] ?? '';
        $addIndexPage = $config['addIndexPage'] ?? false;
        $withHeroFields = $config['withHeroFields'] ?? false;
        $createEntriesField = $config['createEntriesField'] ?? false;
        $template = $config['template'] ?? "ff/sections/$handle";
        $hasUrls = isset($config['hasUrls']) ? $config['hasUrls'] : true;

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
                        'hasUrls' => $hasUrls,
                        'uriFormat' => $hasUrls ? Craft::t('ff-light', $baseUri, language: $site->language) . '/{slug}' : '',
                        'template' => $hasUrls ? $template : ''
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

            $fields = [
                'pageTemplate' => "@ff/_sections/$section->handle/index"
            ];

            $contentService = MainModule::getInstance()->content;

            if ($withHeroFields) {
                $image = $this->getRandomImage($this->minHeroImageWidth);
                $fields['featuredImage'] = $image ? [$image->id] : null;
                $fields['tagline'] = $this->faker->text(40);
            }

            $contentService->createEntry([
                'section' => 'page',
                'type' => 'pageTemplate',
                'title' => $plural,
                'slug' => $baseUri,
                'parent' => $homePage,
                'fields' => $fields,
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

    protected function createMatrixField(array $config): bool{

        $this->fields[$config['handle']] = Craft::$app->fields->getFieldByHandle($config['handle']);
        if ($this->fields[$config['handle']]) {
            return true;
        }

        $blocktypes = [];
        $layoutConfigs = [];
        foreach ($config['blocktypes'] as $blocktypeConfig) {
            $fields = [];
            foreach ($blocktypeConfig['fields'] as $field) {
                $fieldConfig = $field;
                if (isset($fieldConfig['layoutConfig'])) {
                    $layoutConfigs[$fieldConfig['handle']] = $fieldConfig['layoutConfig'];
                    unset($fieldConfig['layoutConfig']);
                }

                $fields[] = Craft::createObject($fieldConfig);
            }
            $blocktype = new MatrixBlockType();
            $blocktype->handle = $blocktypeConfig['handle'];
            $blocktype->name = $blocktypeConfig['name'];
            $layout = $blocktype->getFieldLayout();
            $tab = new FieldLayoutTab();
            $tab->name = 'Content';
            $tab->sortOrder = 1;
            $tab->layout = $layout;
            $tab->setElements(collect($fields)
                ->map(function ($field) use ($layoutConfigs) {
                    $layoutConfig = $layoutConfigs[$field->handle] ?? [];
                    return new CustomField($field, $layoutConfig);
                })
                ->toArray()
            );
            $layout->setTabs([$tab]);

            $blocktypes[] = $blocktype;
        }

        $matrixField = new Matrix();
        $matrixField->handle = $config['handle'];
        $matrixField->name = $config['name'];
        $matrixField->groupId = $config['groupId'];
        $matrixField->setBlockTypes($blocktypes);

        if (!Craft::$app->fields->saveField($matrixField)) {
            $this->logError("Could not save field {$config['handle']}", $matrixField);
            return false;
        }

        $this->fields[$config['handle']] = $field;

        $this->message("Field {$config['name']} created.");
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

    protected function updateElementSource(string $heading, string $sectionHandle, array $tableAttributes)
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


    protected function getFieldGroup(string $fieldGroup)
    {
        return FieldGroupRecord::findOne(['name' => $fieldGroup]);
    }

    protected function message($message)
    {
        if (Craft::$app->request->isConsoleRequest) {
            Console::output($message);
        } else {
            Craft::info($message, '@ff/_install');
        }
    }

    protected function logError(string $message, ?Model $model = null)
    {
        Craft::error($message, $this->logCategory);
        $this->message($message);
        if ($model) {
            Craft::error($model->errors, $this->logCategory);
        }
    }

    protected function getRandomImage($width)
    {
        return Asset::find()
            ->volume($this->imageVolume)
            ->kind('image')
            ->width('> ' . $width)
            ->orderBy(Craft::$app->db->driverName === 'mysql' ? 'RAND()' : 'RANDOM()')
            ->one();
    }
}
