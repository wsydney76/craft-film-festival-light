<?php

namespace wsydney76\ff\console\controllers;

use Craft;
use craft\console\Controller;
use wsydney76\ff\Plugin;
use yii\console\ExitCode;

/**
 * Ff Install controller
 */
class InstallController extends Controller
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
}
