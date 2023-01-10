<?php

namespace wsydney76\ff\controllers;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use wsydney76\package\Plugin;
use function implode;
use function trim;

/**
 * Experimental: controller for package plugin
 * https://github.com/wsydney76/craft-package#use-existing-sections-as-a-package
 */
class ScreeningController extends Controller
{
    public function actionCreate()
    {
        $screening = $this->request->getRequiredBodyParam('screening');
        $section = Craft::$app->sections->getSectionByHandle('screening');

        $this->requirePermission("saveentries:{$section->uid}");

        $films = $screening['films'];

        $entry = new Entry();
        $entry->sectionId = $section->id;


        $fieldHandle = Plugin::getInstance()->packageService->getRelationFieldHandle($films);

        $entry->films = [$films];
        $entry->setFieldValue($fieldHandle, [$films]);
        $entry->locations = [$screening['locations']];
        $entry->remarks = $screening['remarks'];

        // Avoid using current date as default if date is blank
        if (!trim($screening['datetime[date]'])) {
            return $this->asFailure(Craft::t('site', 'Screening Date cannot be blank.'));
        }

        // Avoid using 'midnight' as default if time is blank
        if (!trim($screening['datetime[time]'])) {
            return $this->asFailure(Craft::t('site', 'Screening Time cannot be blank.'));
        }

        // Todo: Test in different preferred locales
        $screeningDateTime = DateTimeHelper::toDateTime([
            'date' => $screening['datetime[date]'],
            'time' => $screening['datetime[time]'],
            'timezone' => $screening['datetime[timezone]'],
        ]);

        // Avoid 'cannot be blank' message if date/time is not empty but could not be converted to a DateTime object.
        if (!$screeningDateTime && ($screening['datetime[date]'] || $screening['datetime[time]'])) {
            return $this->asFailure(Craft::t('site', 'Screening Date Time is not valid.'),);
        }

        $entry->screeningDate = DateTimeHelper::toDateTime([
            'date' => $screening['datetime[date]'],
            'time' => '00:00',
            'timezone' => $screening['datetime[timezone]'],
        ]);
        $entry->screeningTime = $screeningDateTime->format('H:i');

        // Check for double clicks
        if ($entry->screeningDate && $entry->screeningTime) {
            // Todo: Check for overlaps
            $exits = Entry::find()
                ->locations($screening['locations'])
                ->films($screening['films'])
                ->status(null)
                ->drafts(null)
                ->provisionalDrafts(null)
                ->screeningDate($entry->screeningDate)
                ->screeningTime($entry->screeningTime->format('H:i'))
                ->exists();

            if ($exits) {
                return $this->asFailure(Craft::t('site', 'Screening overlaps with existing screening.'));
            }
        }



        $entry->scenario = Element::SCENARIO_LIVE;

        if ($screening['createDraft']) {
            if (!Craft::$app->drafts->saveElementAsDraft($entry, Craft::$app->user->identity->id)) {
                return $this->asFailure(
                    implode(' - ', $entry->getFirstErrors()),
                );
            }
        } else if (!Craft::$app->elements->saveElement($entry)) {
            return $this->asFailure(
                implode(' - ', $entry->getFirstErrors()),
            );
        }

        return $this->asSuccess(Craft::t('site', 'Screening created.'));
    }
}