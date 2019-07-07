<?php

class OaipmhHarvester_Harvest_Mock extends OaipmhHarvester_Harvest_Abstract
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
