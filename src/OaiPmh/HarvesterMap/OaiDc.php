<?php declare(strict_types=1);

/**
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @copyright Daniel Berthereau, 2014-2023
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

use SimpleXMLElement;

/**
 * Metadata format map for the required oai_dc Dublin Core format
 */
class OaiDc extends AbstractHarvesterMap
{
    const METADATA_PREFIX = 'oai_dc';
    const NAMESPACE_OAI_DC = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    const NAMESPACE_DUBLIN_CORE = 'http://purl.org/dc/elements/1.1/';

    /**
     * Dublin Core lower case elements without prefix.
     *
     * The key is the static id in the omeka database.
     * The order is the official one, used in Omeka and oai_dc.
     *
     * @var array
     */
    const DUBLIN_CORE_ELEMENTS = [
        1 => 'title',
        2 => 'creator',
        3 => 'subject',
        4 => 'description',
        5 => 'publisher',
        6 => 'contributor',
        7 => 'date',
        8 => 'type',
        9 => 'format',
        10 => 'identifier',
        11 => 'source',
        12 => 'language',
        13 => 'relation',
        14 => 'coverage',
        15 => 'rights',
    ];

    protected function mapRecordSingle(SimpleXMLElement $record, array $resource): array
    {
        $metadata = $record
            ->metadata
            ->children(self::NAMESPACE_OAI_DC)
            ->children(self::NAMESPACE_DUBLIN_CORE);

        foreach (self::DUBLIN_CORE_ELEMENTS as $localName) {
            if (isset($metadata->$localName)) {
                $resource["dcterms:$localName"] = $this->extractValues($metadata, "dcterms:$localName");
            }
        }
        return $resource;
    }
}
