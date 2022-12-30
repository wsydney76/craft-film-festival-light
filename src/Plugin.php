<?php

namespace wsydney76\ff;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\App;
use craft\web\View;
use wsydney76\ff\models\Settings;
use wsydney76\ff\services\MigrationService;
use yii\base\Event;

/**
 * Film Festival plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author wsydney76
 * @copyright wsydney76
 * @license MIT
 * @property-read MigrationService $migrationService
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => ['migrationService' => MigrationService::class],
        ];
    }

    public function init()
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('ff/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['ff'] = $this->getBasePath() . '/templates';
                $event->roots['@ff'] = $this->getBasePath() . '/templates';
            }
        );
    }
}
