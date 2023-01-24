<?php

namespace wsydney76\ff\console\controllers;

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

    /**
     * ff/ff-install command
     */
    public function actionIndex(): int
    {
        Plugin::getInstance()->migrationService->install();

        return ExitCode::OK;
    }

}
