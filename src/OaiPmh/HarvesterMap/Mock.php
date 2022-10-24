<?php declare(strict_types=1);

namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

class Mock extends AbstractHarvesterMap
{
    const METADATA_PREFIX = 'mock';
    const METADATA_SCHEMA = 'mock.schema';

    protected function _harvestRecord($record)
    {
        return [
            'itemMetadata' => [
                'public' => $this->getOption('public'),
            ],
            'elementTexts' => [
                'Dublin Core' => [
                    'Title' => [
                        ['text' => 'Mock Title', 'html' => 0],
                    ],
                ],
            ],
            'fileMetadata' => [],
        ];
    }
}
