<?php declare(strict_types=1);

namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

use SimpleXMLElement;

class Mock extends AbstractHarvesterMap
{
    const METADATA_PREFIX = 'mock';
    const METADATA_SCHEMA = 'mock.schema';

    protected function mapRecordSingle(SimpleXMLElement $record, array $resource): array
    {
        $resource['dcterms:title'][] = [
            'type' => 'literal',
            'property_id' => 1,
            'is_public' => true,
            '@value' => 'Mock Title',
        ];
        return $resource;
    }
}
