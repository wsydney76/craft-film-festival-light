<?php

namespace wsydney76\ff\behaviors;

use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use yii\base\Behavior;

class EntryBehavior extends Behavior
{
    public function getScreenings(): EntryQuery
    {
        /** @var Entry $entry */
        $entry = $this->owner;

        $query = Entry::find()
            ->section('screening')
            ->orderBy('screeningDateTime');

        switch ($entry->section->handle) {

            case 'film':
            {
                $query
                    ->relatedTo(['targetElement' => $entry->id, 'field' => 'films'])
                    ->with('locations');
                break;
            }

            case 'location':
            {
                $query
                    ->relatedTo(['targetElement' => $entry->id, 'field' => 'locations'])
                    ->with('films');
                break;
            }

            default:
            {

                $query
                    ->relatedTo([
                        'targetElement' => Entry::find()
                            ->section('film')
                            ->relatedTo(['targetElement' => $entry->id])
                            ->ids(),
                        'field' => 'films'
                    ])
                    ->with([
                        ['films'],
                        ['locations']
                    ]);
                break;
            }


        }

        return $query;
    }
}